<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QamsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_core_entities_and_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $classResponse = $this->postJson('/admin/qams/classes', ['name' => 'Class 10']);
        $classResponse->assertCreated();
        $classId = $classResponse->json('id');

        $subjectResponse = $this->postJson('/admin/qams/subjects', [
            'school_class_id' => $classId,
            'name' => 'Mathematics',
        ]);
        $subjectResponse->assertCreated();
        $subjectId = $subjectResponse->json('id');

        $teacherResponse = $this->postJson('/admin/qams/teachers', [
            'name' => 'Teacher One',
            'user_name' => 'teacher1',
            'password' => 'password',
            'job_history' => '5 years',
            'education' => 'MSc Math',
        ]);
        $teacherResponse->assertCreated();
        $teacherId = $teacherResponse->json('id');

        $this->postJson("/admin/qams/subjects/{$subjectId}/assign-teacher", ['teacher_id' => $teacherId])
            ->assertOk();

        $studentResponse = $this->postJson('/admin/qams/students', [
            'name' => 'Student One',
            'user_name' => 'student1',
            'password' => 'password',
            'admission_number' => 'ADM-001',
            'father_name' => 'Father One',
            'school_class_id' => $classId,
            'subject_ids' => [$subjectId],
        ]);
        $studentResponse->assertCreated();
        $studentId = $studentResponse->json('id');

        $this->putJson("/admin/qams/students/{$studentId}", [
            'name' => 'Student One Updated',
            'user_name' => 'student1',
            'admission_number' => 'ADM-001',
            'father_name' => 'Father Updated',
            'school_class_id' => $classId,
            'subject_ids' => [$subjectId],
        ])->assertOk();

        $this->getJson('/admin/qams/reports')
            ->assertOk()
            ->assertJson([
                'students_count' => 1,
                'teachers_count' => 1,
                'subjects_count' => 1,
                'classes_count' => 1,
            ]);
    }

    public function test_teacher_student_quiz_assignment_and_auto_zero_flows(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-06 10:00:00'));

        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->create();

        $class = SchoolClass::create(['name' => 'Class 9']);
        $subject = Subject::create(['name' => 'Science', 'school_class_id' => $class->id]);
        $subject->teachers()->attach($teacher->id);
        $subject->students()->attach($student->id);

        $this->actingAs($teacher)->postJson('/teacher/qams/question-bank', [
            'subject_id' => $subject->id,
            'question' => '2 + 2 = ?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_option' => 'B',
        ])->assertCreated();

        $question = QuestionBankItem::first();

        $quizResponse = $this->actingAs($teacher)->postJson('/teacher/qams/quizzes', [
            'subject_id' => $subject->id,
            'title' => 'Weekly Quiz',
            'starts_at' => Carbon::now()->subHour()->toDateTimeString(),
            'deadline' => Carbon::now()->addHour()->toDateTimeString(),
        ])->assertCreated();
        $quiz = Quiz::findOrFail($quizResponse->json('id'));
        $this->actingAs($teacher)->postJson("/teacher/qams/quizzes/{$quiz->id}/publish")->assertOk();

        $this->actingAs($student)->postJson("/student/qams/quizzes/{$quiz->id}/attempt", [
            'answers' => [
                ['question_id' => $question->id, 'selected_option' => 'B'],
            ],
        ])->assertCreated()->assertJson(['score' => 1]);

        $assignmentResponse = $this->actingAs($teacher)->postJson('/teacher/qams/assignments', [
            'subject_id' => $subject->id,
            'title' => 'Lab Report',
            'description' => 'Submit report',
            'deadline' => Carbon::now()->addHour()->toDateTimeString(),
        ])->assertCreated();
        $assignment = Assignment::findOrFail($assignmentResponse->json('id'));
        $this->actingAs($teacher)->postJson("/teacher/qams/assignments/{$assignment->id}/publish")->assertOk();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        Carbon::setTestNow(Carbon::now()->addHours(2));
        $this->actingAs($student)->getJson('/student/qams/results')->assertOk();
        $submission->refresh();
        $this->assertSame('auto_zero', $submission->status);
        $this->assertSame(0, $submission->marks);

        $this->actingAs($teacher)->postJson("/teacher/qams/assignment-submissions/{$submission->id}/grade", [
            'marks' => 85,
            'feedback' => 'Good work',
        ])->assertOk();

        $this->actingAs($student)->getJson('/student/qams/results')
            ->assertOk()
            ->assertJsonPath('quiz_results.0.score', 1);

        $this->actingAs($teacher)->getJson('/teacher/qams/reports/performance?subject_id='.$subject->id)
            ->assertOk()
            ->assertJsonPath('subject_id', $subject->id);

        // admin can still block/unblock users via existing functionality
        $this->actingAs($admin)->post("/admin/users/{$student->id}/toggle-block")->assertRedirect();
        $student->refresh();
        $this->assertSame('0', $student->active);
    }

    public function test_teacher_cannot_access_unassigned_subject_data(): void
    {
        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();
        $class = SchoolClass::create(['name' => 'Class 8']);
        $subject = Subject::create(['name' => 'English', 'school_class_id' => $class->id]);
        $subject->teachers()->attach($otherTeacher->id);

        $this->actingAs($teacher)->postJson('/teacher/qams/question-bank', [
            'subject_id' => $subject->id,
            'question' => 'A?',
            'option_a' => '1',
            'option_b' => '2',
            'option_c' => '3',
            'option_d' => '4',
            'correct_option' => 'A',
        ])->assertForbidden();
    }
}
