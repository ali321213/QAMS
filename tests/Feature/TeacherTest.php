<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Teacher tests — covers Teacher requirements #1–8 (login covered in AuthTest)
 *
 * Tests:
 *  #2  - Create question in bank / reject unassigned subject
 *  #3  - Create quiz / publish quiz
 *  #4  - Create assignment (auto-creates student submissions)
 *  #5  - Extend quiz deadline / extend assignment deadline
 *  #6  - Cannot access another teacher's quiz/assignment
 *  #7  - Publish quiz makes it visible to students
 *  #8  - Grade assignment submission
 *  #9  - Performance report (quiz & assignment averages)
 *       - Bug 3 regression: teacher with no subjects does not crash
 */
class TeacherTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $student;
    private SchoolClass $class;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-04-16 09:00:00'));

        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create(['role' => 'student']);
        $this->class   = SchoolClass::create(['name' => 'Class 10']);
        $this->subject = Subject::create(['name' => 'Mathematics', 'school_class_id' => $this->class->id]);

        $this->subject->teachers()->attach($this->teacher->id);
        $this->subject->students()->attach($this->student->id);
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Question bank
    // ─────────────────────────────────────────────

    public function test_teacher_can_add_question_to_assigned_subject(): void
    {
        $this->actingAs($this->teacher)
            ->postJson('/teacher/qams/question-bank', [
                'subject_id'     => $this->subject->id,
                'question'       => 'What is 2+2?',
                'option_a'       => '3',
                'option_b'       => '4',
                'option_c'       => '5',
                'option_d'       => '6',
                'correct_option' => 'B',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('question_bank_items', [
            'question'       => 'What is 2+2?',
            'correct_option' => 'B',
            'teacher_id'     => $this->teacher->id,
        ]);
    }

    public function test_teacher_cannot_add_question_to_unassigned_subject(): void
    {
        $otherSubject = Subject::create(['name' => 'English', 'school_class_id' => $this->class->id]);

        $this->actingAs($this->teacher)
            ->postJson('/teacher/qams/question-bank', [
                'subject_id'     => $otherSubject->id,
                'question'       => 'A question?',
                'option_a'       => '1',
                'option_b'       => '2',
                'option_c'       => '3',
                'option_d'       => '4',
                'correct_option' => 'A',
            ])
            ->assertForbidden();
    }

    public function test_question_bank_index_only_shows_assigned_subjects(): void
    {
        $otherTeacher  = User::factory()->teacher()->create();
        $otherSubject  = Subject::create(['name' => 'Chemistry', 'school_class_id' => $this->class->id]);
        $otherSubject->teachers()->attach($otherTeacher->id);

        QuestionBankItem::create([
            'subject_id'     => $otherSubject->id,
            'teacher_id'     => $otherTeacher->id,
            'question'       => 'Hidden question',
            'option_a'       => 'a', 'option_b' => 'b', 'option_c' => 'c', 'option_d' => 'd',
            'correct_option' => 'A',
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/question-bank');
        $response->assertOk()->assertDontSee('Hidden question');
    }

    // ─────────────────────────────────────────────
    // Requirement #3 — Conduct quizzes
    // ─────────────────────────────────────────────

    public function test_teacher_can_create_a_quiz(): void
    {
        $this->actingAs($this->teacher)
            ->postJson('/teacher/qams/quizzes', [
                'subject_id' => $this->subject->id,
                'title'      => 'Weekly Quiz',
                'starts_at'  => Carbon::now()->toDateTimeString(),
                'deadline'   => Carbon::now()->addHour()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonFragment(['title' => 'Weekly Quiz', 'published' => false]);

        $this->assertDatabaseHas('quizzes', ['title' => 'Weekly Quiz', 'published' => 0]);
    }

    public function test_teacher_can_publish_a_quiz(): void
    {
        $quiz = Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Draft Quiz',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => false,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/quizzes/{$quiz->id}/publish")
            ->assertOk();

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'published' => 1]);
    }

    public function test_teacher_cannot_publish_another_teachers_quiz(): void
    {
        $other = User::factory()->teacher()->create();
        $this->subject->teachers()->attach($other->id);

        $quiz = Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $other->id,
            'title'      => 'Other Quiz',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => false,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/quizzes/{$quiz->id}/publish")
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // Requirement #5 — Extend quiz deadline
    // ─────────────────────────────────────────────

    public function test_teacher_can_extend_quiz_deadline(): void
    {
        $quiz = Quiz::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Quiz',
            'starts_at'  => Carbon::now(),
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ]);

        $newDeadline = Carbon::now()->addHours(3)->toDateTimeString();

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/quizzes/{$quiz->id}/extend", ['deadline' => $newDeadline])
            ->assertOk();

        $this->assertDatabaseHas('quizzes', [
            'id'       => $quiz->id,
            'deadline' => $newDeadline,
        ]);
    }

    // ─────────────────────────────────────────────
    // Requirement #4 — Upload assignments
    // ─────────────────────────────────────────────

    public function test_teacher_can_create_assignment(): void
    {
        $this->actingAs($this->teacher)
            ->postJson('/teacher/qams/assignments', [
                'subject_id'  => $this->subject->id,
                'title'       => 'Lab Report',
                'description' => 'Write a report on gravity.',
                'deadline'    => Carbon::now()->addDay()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonFragment(['title' => 'Lab Report']);

        $this->assertDatabaseHas('assignments', ['title' => 'Lab Report', 'published' => 0]);
    }

    public function test_creating_assignment_auto_creates_student_submission_records(): void
    {
        $response = $this->actingAs($this->teacher)
            ->postJson('/teacher/qams/assignments', [
                'subject_id' => $this->subject->id,
                'title'      => 'Homework',
                'deadline'   => Carbon::now()->addDay()->toDateTimeString(),
            ])
            ->assertCreated();

        $assignmentId = $response->json('id');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignmentId,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
        ]);
    }

    public function test_teacher_can_publish_assignment(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Draft Assignment',
            'deadline'   => Carbon::now()->addDay(),
            'published'  => false,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/assignments/{$assignment->id}/publish")
            ->assertOk();

        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'published' => 1]);
    }

    // ─────────────────────────────────────────────
    // Requirement #5 — Extend assignment deadline
    // ─────────────────────────────────────────────

    public function test_teacher_can_extend_assignment_deadline(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Assignment',
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ]);

        $newDeadline = Carbon::now()->addDays(2)->toDateTimeString();

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/assignments/{$assignment->id}/extend", ['deadline' => $newDeadline])
            ->assertOk();

        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'deadline' => $newDeadline]);
    }

    public function test_teacher_cannot_extend_another_teachers_assignment(): void
    {
        $other = User::factory()->teacher()->create();
        $this->subject->teachers()->attach($other->id);

        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $other->id,
            'title'      => 'Other Assignment',
            'deadline'   => Carbon::now()->addHour(),
            'published'  => true,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/assignments/{$assignment->id}/extend", [
                'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
            ])
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // Requirement #8 — Grade assignment
    // ─────────────────────────────────────────────

    public function test_teacher_can_grade_assignment_submission(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Essay',
            'deadline'   => Carbon::now()->addDay(),
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/assignment-submissions/{$submission->id}/grade", [
                'marks'    => 78,
                'feedback' => 'Good work, minor errors.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('assignment_submissions', [
            'id'       => $submission->id,
            'marks'    => 78,
            'status'   => 'graded',
            'feedback' => 'Good work, minor errors.',
        ]);
    }

    public function test_grade_marks_must_be_between_0_and_100(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Essay',
            'deadline'   => Carbon::now()->addDay(),
            'published'  => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
        ]);

        $this->actingAs($this->teacher)
            ->postJson("/teacher/qams/assignment-submissions/{$submission->id}/grade", [
                'marks' => 150,
            ])
            ->assertUnprocessable();
    }

    // ─────────────────────────────────────────────
    // Requirement #9 — Performance report
    // ─────────────────────────────────────────────

    public function test_teacher_can_view_performance_report_for_assigned_subject(): void
    {
        $this->actingAs($this->teacher)
            ->getJson('/teacher/qams/reports/performance?subject_id=' . $this->subject->id)
            ->assertOk()
            ->assertJsonStructure(['subject_id', 'quiz_average', 'assignment_average', 'generated_at']);
    }

    public function test_teacher_cannot_view_performance_report_for_unassigned_subject(): void
    {
        $other = Subject::create(['name' => 'History', 'school_class_id' => $this->class->id]);

        $this->actingAs($this->teacher)
            ->getJson('/teacher/qams/reports/performance?subject_id=' . $other->id)
            ->assertForbidden();
    }

    /**
     * Bug 3 regression — teacher with no subjects must not crash on performance report page.
     */
    public function test_performance_report_page_does_not_crash_when_teacher_has_no_subjects(): void
    {
        $newTeacher = User::factory()->teacher()->create();

        $this->actingAs($newTeacher)
            ->get('/teacher/reports/performance')
            ->assertOk();
    }

    // ─────────────────────────────────────────────
    // Requirement #6 — Submissions page
    // ─────────────────────────────────────────────

    public function test_teacher_can_view_assignment_submissions(): void
    {
        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title'      => 'Project',
            'deadline'   => Carbon::now()->addDay(),
            'published'  => true,
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $this->student->id,
            'status'        => 'pending',
            'marks'         => 0,
        ]);

        $this->actingAs($this->teacher)
            ->get("/teacher/assignments/{$assignment->id}/submissions")
            ->assertOk()
            ->assertSee($this->student->name);
    }

    public function test_teacher_cannot_view_another_teachers_submissions(): void
    {
        $other = User::factory()->teacher()->create();
        $this->subject->teachers()->attach($other->id);

        $assignment = Assignment::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $other->id,
            'title'      => 'Other Project',
            'deadline'   => Carbon::now()->addDay(),
            'published'  => true,
        ]);

        $this->actingAs($this->teacher)
            ->get("/teacher/assignments/{$assignment->id}/submissions")
            ->assertForbidden();
    }
}
