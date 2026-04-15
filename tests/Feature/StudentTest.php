<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Student tests — covers Student requirements #1–3 (login covered in AuthTest)
 *
 * Tests:
 *  #2  - Can see published quizzes for enrolled subjects
 *  #2  - Cannot see unpublished quizzes
 *  #2  - Can attempt a quiz within deadline (auto-marked)
 *  #2  - Cannot attempt the same quiz twice
 *  #2  - Cannot attempt quiz after deadline
 *  #2  - Cannot attempt quiz for un-enrolled subject
 *  #3  - Can submit assignment before deadline
 *  #3  - Submitting after deadline marks submission as auto_zero
 *  #3  - Cannot submit assignment for un-enrolled subject
 *  #4  - Can view all their quiz and assignment results
 *       - Bug 1 regression: quiz attempt page loads without error
 */
class StudentTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $student;
    private SchoolClass $class;
    private Subject $subject;
    private QuestionBankItem $question;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-04-16 10:00:00'));
        Storage::fake('public');

        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create(['role' => 'student']);
        $this->class   = SchoolClass::create(['name' => 'Class 10']);
        $this->subject = Subject::create(['name' => 'Science', 'school_class_id' => $this->class->id]);

        $this->subject->teachers()->attach($this->teacher->id);
        $this->subject->students()->attach($this->student->id);

        $this->question = QuestionBankItem::create([
            'subject_id'     => $this->subject->id,
            'teacher_id'     => $this->teacher->id,
            'question'       => 'What is H2O?',
            'option_a'       => 'Salt',
            'option_b'       => 'Water',
            'option_c'       => 'Sugar',
            'option_d'       => 'Acid',
            'correct_option' => 'B',
        ]);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function makePublishedQuiz(array $overrides = []): Quiz
    {
        return Quiz::create(array_merge([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Test Quiz',
            'starts_at'  => Carbon::now()->subMinute(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ], $overrides));
    }

    private function makePublishedAssignment(array $overrides = []): Assignment
    {
        return Assignment::create(array_merge([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Test Assignment',
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ], $overrides));
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Quizzes (view)
    // ─────────────────────────────────────────────

    public function test_student_can_see_published_quizzes_for_enrolled_subjects(): void
    {
        $quiz = $this->makePublishedQuiz(['title' => 'Visible Quiz']);

        $this->actingAs($this->student)
            ->get('/student/quizzes')
            ->assertOk()
            ->assertSee('Visible Quiz');
    }

    public function test_student_cannot_see_unpublished_quiz(): void
    {
        Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Hidden Draft Quiz',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => false,
        ]);

        $this->actingAs($this->student)
            ->get('/student/quizzes')
            ->assertOk()
            ->assertDontSee('Hidden Draft Quiz');
    }

    public function test_student_cannot_see_quizzes_for_unenrolled_subjects(): void
    {
        $otherSubject = Subject::create(['name' => 'History', 'school_class_id' => $this->class->id]);
        Quiz::create([
            'subject_id' => $otherSubject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Other Subject Quiz',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ]);

        $this->actingAs($this->student)
            ->get('/student/quizzes')
            ->assertOk()
            ->assertDontSee('Other Subject Quiz');
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Quiz attempt page (Bug 1 regression)
    // ─────────────────────────────────────────────

    public function test_quiz_attempt_page_loads_without_error(): void
    {
        $quiz = $this->makePublishedQuiz();

        // This page previously crashed with a PHP parse error (Bug 1).
        $this->actingAs($this->student)
            ->get("/student/quizzes/{$quiz->id}/attempt")
            ->assertOk()
            ->assertSee('What is H2O?');
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Attempt quiz (auto-marked)
    // ─────────────────────────────────────────────

    public function test_student_can_attempt_quiz_and_score_is_calculated(): void
    {
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $this->question->id, 'selected_option' => 'B'], // correct
                ],
            ])
            ->assertCreated();

        $this->assertSame(1, $response->json('score'));

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id'    => $quiz->id,
            'student_id' => $this->student->id,
            'score'      => 1,
        ]);
    }

    public function test_wrong_answer_scores_zero(): void
    {
        $quiz = $this->makePublishedQuiz();

        $response = $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $this->question->id, 'selected_option' => 'A'], // wrong
                ],
            ])
            ->assertCreated();

        $this->assertSame(0, $response->json('score'));
    }

    public function test_student_cannot_attempt_same_quiz_twice(): void
    {
        $quiz = $this->makePublishedQuiz();

        QuizAttempt::create([
            'quiz_id'         => $quiz->id,
            'student_id'      => $this->student->id,
            'score'           => 1,
            'total_questions' => 1,
            'submitted_at'    => Carbon::now(),
        ]);

        $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $this->question->id, 'selected_option' => 'B'],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_student_cannot_attempt_quiz_after_deadline(): void
    {
        $quiz = $this->makePublishedQuiz([
            'starts_at' => Carbon::now()->subHours(3),
            'deadline'  => Carbon::now()->subHour(), // deadline passed
        ]);

        $this->actingAs($this->student)
            ->get("/student/quizzes/{$quiz->id}/attempt")
            ->assertForbidden();
    }

    public function test_student_cannot_attempt_quiz_for_unenrolled_subject(): void
    {
        $outsider = User::factory()->create(['role' => 'student']); // not enrolled

        $quiz = $this->makePublishedQuiz();

        $this->actingAs($outsider)
            ->get("/student/quizzes/{$quiz->id}/attempt")
            ->assertForbidden();
    }

    public function test_student_cannot_attempt_unpublished_quiz(): void
    {
        $quiz = Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Draft',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => false,
        ]);

        $this->actingAs($this->student)
            ->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
                'answers' => [
                    ['question_id' => $this->question->id, 'selected_option' => 'B'],
                ],
            ])
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // Requirement #3 — Assignment submission
    // ─────────────────────────────────────────────

    public function test_student_can_submit_assignment_before_deadline(): void
    {
        $assignment = $this->makePublishedAssignment();
        $file       = UploadedFile::fake()->create('solution.pdf', 512, 'application/pdf');

        $this->actingAs($this->student)
            ->post("/student/qams/assignments/{$assignment->id}/submit", [
                'file' => $file,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
        ]);
    }

    public function test_student_submitting_after_deadline_gets_auto_zero_status(): void
    {
        $assignment = $this->makePublishedAssignment([
            'deadline' => Carbon::now()->subHour(), // deadline already passed
        ]);

        $file = UploadedFile::fake()->create('late.pdf', 100, 'application/pdf');

        $this->actingAs($this->student)
            ->post("/student/qams/assignments/{$assignment->id}/submit", [
                'file' => $file,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'auto_zero',
            'marks'         => 0,
        ]);
    }

    public function test_student_cannot_submit_for_unenrolled_subject(): void
    {
        $outsider   = User::factory()->create(['role' => 'student']);
        $assignment = $this->makePublishedAssignment();
        $file       = UploadedFile::fake()->create('x.pdf', 100, 'application/pdf');

        $this->actingAs($outsider)
            ->post("/student/qams/assignments/{$assignment->id}/submit", [
                'file' => $file,
            ])
            ->assertForbidden();
    }

    public function test_student_cannot_submit_to_unpublished_assignment(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Draft',
            'deadline'   => Carbon::now()->addHour(),
            'published'  => false,
        ]);

        $file = UploadedFile::fake()->create('x.pdf', 100, 'application/pdf');

        $this->actingAs($this->student)
            ->post("/student/qams/assignments/{$assignment->id}/submit", [
                'file' => $file,
            ])
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // Requirement #3 — View assignment
    // ─────────────────────────────────────────────

    public function test_student_can_view_assignment_details(): void
    {
        $assignment = $this->makePublishedAssignment(['title' => 'Essay Task']);

        $this->actingAs($this->student)
            ->get("/student/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Essay Task');
    }

    // ─────────────────────────────────────────────
    // Requirement #4 — Results
    // ─────────────────────────────────────────────

    public function test_student_can_view_quiz_and_assignment_results(): void
    {
        $quiz = $this->makePublishedQuiz();
        QuizAttempt::create([
            'quiz_id'         => $quiz->id,
            'student_id'      => $this->student->id,
            'score'           => 1,
            'total_questions' => 1,
            'submitted_at'    => Carbon::now(),
        ]);

        $assignment = $this->makePublishedAssignment();
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'graded',
            'marks'         => 90,
        ]);

        $this->actingAs($this->student)
            ->getJson('/student/qams/results')
            ->assertOk()
            ->assertJsonPath('quiz_results.0.score', 1)
            ->assertJsonPath('assignment_results.0.marks', 90);
    }

    public function test_student_can_view_results_page(): void
    {
        $this->actingAs($this->student)
            ->get('/student/results')
            ->assertOk()
            ->assertSee('Quiz results')
            ->assertSee('Assignments');
    }

    public function test_results_only_show_own_records(): void
    {
        $otherStudent = User::factory()->create(['role' => 'student']);
        $this->subject->students()->attach($otherStudent->id);

        $quiz = $this->makePublishedQuiz();
        QuizAttempt::create([
            'quiz_id'         => $quiz->id,
            'student_id'      => $otherStudent->id,
            'score'           => 1,
            'total_questions' => 1,
            'submitted_at'    => Carbon::now(),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/student/qams/results');

        $response->assertOk();
        $this->assertEmpty($response->json('quiz_results'));
    }
}
