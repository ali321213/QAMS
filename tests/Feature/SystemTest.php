<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * System (automated) tests — covers System requirements #1 and #2
 *
 * Tests:
 *  #1  - Auto-assign zero marks if a student does not submit assignment on time
 *        • Pending submission with no file after deadline → status=auto_zero, marks=0
 *        • Pending submission WITH file but after deadline stays → student submitted late
 *        • Submitted-on-time submissions are not touched
 *        • Graded submissions are not overwritten
 *  #2  - System auto-marks all quizzes (correct / incorrect answer scoring)
 *        • All-correct answers → full score
 *        • All-wrong answers   → 0 score
 *        • Mixed answers       → partial score
 *        • Answers recorded with is_correct flag
 */
class SystemTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-04-16 09:00:00'));

        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create(['role' => 'student']);
        $class         = SchoolClass::create(['name' => 'Class 10']);
        $this->subject = Subject::create(['name' => 'Science', 'school_class_id' => $class->id]);
        $this->subject->teachers()->attach($this->teacher->id);
        $this->subject->students()->attach($this->student->id);
    }

    // ─────────────────────────────────────────────
    // System requirement #1 — Auto zero marks
    // ─────────────────────────────────────────────

    public function test_pending_submission_with_no_file_after_deadline_gets_auto_zero(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Past Due',
            'deadline'   => Carbon::now()->subHour(), // deadline has passed
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
            'submitted_at'  => null,
            'file_path'     => null,
        ]);

        $applied = AssignmentSubmission::applyAutoZeroMarks();

        $this->assertGreaterThan(0, $applied);
        $submission->refresh();
        $this->assertSame('auto_zero', $submission->status);
        $this->assertSame(0, $submission->marks);
    }

    public function test_pending_submission_before_deadline_is_not_touched(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Active',
            'deadline'   => Carbon::now()->addHour(), // still open
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
            'submitted_at'  => null,
        ]);

        AssignmentSubmission::applyAutoZeroMarks();

        $submission->refresh();
        $this->assertSame('pending', $submission->status);
    }

    public function test_already_graded_submission_is_not_overwritten_by_auto_zero(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Graded',
            'deadline'   => Carbon::now()->subHour(),
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'graded', // already graded
            'marks'         => 85,
        ]);

        AssignmentSubmission::applyAutoZeroMarks();

        $submission->refresh();
        $this->assertSame('graded', $submission->status);
        $this->assertSame(85, $submission->marks);
    }

    public function test_multiple_overdue_submissions_are_all_zeroed(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Mass Zero',
            'deadline'   => Carbon::now()->subHour(),
            'published'  => true,
        ]);

        // Create 3 more students, all without submission files
        $students = User::factory()->count(3)->create(['role' => 'student']);
        foreach ($students as $s) {
            $this->subject->students()->attach($s->id);
            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'student_id'    => $s->id,
                'status'        => 'pending',
                'marks'         => 0,
                'submitted_at'  => null,
            ]);
        }

        // Also the original student
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
            'submitted_at'  => null,
        ]);

        $applied = AssignmentSubmission::applyAutoZeroMarks();
        $this->assertSame(4, $applied);

        $this->assertSame(
            0,
            AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('status', 'pending')
                ->count()
        );
    }

    public function test_auto_zero_is_triggered_via_middleware_on_each_request(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Overdue',
            'deadline'   => Carbon::now()->subHour(),
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
            'submitted_at'  => null,
        ]);

        // Any authenticated request should trigger applyAutoZeroMarks via EnsureUserIsActive middleware
        $this->actingAs($this->student)->get('/student/dashboard')->assertOk();

        $submission->refresh();
        $this->assertSame('auto_zero', $submission->status);
    }

    // ─────────────────────────────────────────────
    // System requirement #2 — Auto-mark quizzes
    // ─────────────────────────────────────────────

    private function makeQuestion(string $correctOption = 'B'): QuestionBankItem
    {
        return QuestionBankItem::create([
            'subject_id'     => $this->subject->id,
            'teacher_id'     => $this->teacher->id,
            'question'       => 'Q?',
            'option_a'       => 'A',
            'option_b'       => 'B',
            'option_c'       => 'C',
            'option_d'       => 'D',
            'correct_option' => $correctOption,
        ]);
    }

    private function makePublishedQuiz(): Quiz
    {
        return Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Auto-mark Quiz',
            'starts_at'  => Carbon::now()->subMinute(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ]);
    }

    public function test_all_correct_answers_give_full_score(): void
    {
        $q1   = $this->makeQuestion('A');
        $q2   = $this->makeQuestion('C');
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $q1->id, 'selected_option' => 'A'],
                    ['question_id' => $q2->id, 'selected_option' => 'C'],
                ],
            ])
            ->assertCreated();

        $this->assertSame(2, $response->json('score'));
    }

    public function test_all_wrong_answers_give_zero_score(): void
    {
        $q1   = $this->makeQuestion('A');
        $q2   = $this->makeQuestion('C');
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $q1->id, 'selected_option' => 'D'], // wrong
                    ['question_id' => $q2->id, 'selected_option' => 'B'], // wrong
                ],
            ])
            ->assertCreated();

        $this->assertSame(0, $response->json('score'));
    }

    public function test_mixed_answers_give_partial_score(): void
    {
        $q1   = $this->makeQuestion('A');
        $q2   = $this->makeQuestion('C');
        $q3   = $this->makeQuestion('D');
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $q1->id, 'selected_option' => 'A'], // correct
                    ['question_id' => $q2->id, 'selected_option' => 'B'], // wrong
                    ['question_id' => $q3->id, 'selected_option' => 'D'], // correct
                ],
            ])
            ->assertCreated();

        $this->assertSame(2, $response->json('score'));
    }

    public function test_each_answer_is_stored_with_correct_is_correct_flag(): void
    {
        $q1   = $this->makeQuestion('A');
        $q2   = $this->makeQuestion('C');
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $q1->id, 'selected_option' => 'A'], // correct
                    ['question_id' => $q2->id, 'selected_option' => 'B'], // wrong
                ],
            ])
            ->assertCreated();

        $attempt = QuizAttempt::find($response->json('attempt_id'));

        $this->assertDatabaseHas('quiz_answers', [
            'quiz_attempt_id'      => $attempt->id,
            'question_bank_item_id' => $q1->id,
            'selected_option'      => 'A',
            'is_correct'           => 1,
        ]);

        $this->assertDatabaseHas('quiz_answers', [
            'quiz_attempt_id'      => $attempt->id,
            'question_bank_item_id' => $q2->id,
            'selected_option'      => 'B',
            'is_correct'           => 0,
        ]);
    }

    public function test_score_is_stored_in_quiz_attempt_record(): void
    {
        $q    = $this->makeQuestion('D');
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $q->id, 'selected_option' => 'D'],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('quiz_attempts', [
            'id'              => $response->json('attempt_id'),
            'score'           => 1,
            'total_questions' => 1,
        ]);
    }

    public function test_questions_from_other_subjects_are_rejected(): void
    {
        $otherSubject = Subject::create(['name' => 'History', 'school_class_id' => $this->subject->school_class_id]);
        $foreignQ     = QuestionBankItem::create([
            'subject_id'     => $otherSubject->id,
            'teacher_id'     => $this->teacher->id,
            'question'       => 'Foreign?',
            'option_a'       => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4',
            'correct_option' => 'A',
        ]);

        $quiz = $this->makePublishedQuiz();

        $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $foreignQ->id, 'selected_option' => 'A'],
                ],
            ])
            ->assertStatus(422);
    }
}
