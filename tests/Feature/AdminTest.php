<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin tests — covers Admin requirements #1–8
 *
 * Tests:
 *  #2  - Add class / update class
 *  #2  - Add subject / update subject
 *  #3  - Register student (name, admission_number, father_name, picture, class, subjects)
 *  #4  - Register teacher (name, job_history, education)
 *  #5  - Update student info / update teacher info
 *  #6  - Assign subject to teacher
 *  #7  - Block and unblock accounts / cannot block self
 *  #8  - Generate reports (student, teacher, subject, class counts)
 *       - Search works across all roles (regression for Bug 2 fix)
 */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Classes
    // ─────────────────────────────────────────────

    public function test_admin_can_create_a_class(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/qams/classes', ['name' => 'Class 10'])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Class 10']);

        $this->assertDatabaseHas('school_classes', ['name' => 'Class 10']);
    }

    public function test_admin_cannot_create_duplicate_class(): void
    {
        SchoolClass::create(['name' => 'Class 10']);

        $this->actingAs($this->admin)
            ->postJson('/admin/qams/classes', ['name' => 'Class 10'])
            ->assertUnprocessable();
    }

    public function test_admin_can_update_a_class(): void
    {
        $class = SchoolClass::create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->putJson("/admin/qams/classes/{$class->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('school_classes', ['name' => 'New Name']);
    }

    // ─────────────────────────────────────────────
    // Requirement #2 — Subjects
    // ─────────────────────────────────────────────

    public function test_admin_can_create_a_subject(): void
    {
        $class = SchoolClass::create(['name' => 'Class 9']);

        $this->actingAs($this->admin)
            ->postJson('/admin/qams/subjects', [
                'school_class_id' => $class->id,
                'name'            => 'Mathematics',
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Mathematics']);

        $this->assertDatabaseHas('subjects', ['name' => 'Mathematics', 'school_class_id' => $class->id]);
    }

    public function test_admin_can_update_a_subject(): void
    {
        $class   = SchoolClass::create(['name' => 'Class 9']);
        $subject = Subject::create(['name' => 'Old Subject', 'school_class_id' => $class->id]);

        $this->actingAs($this->admin)
            ->putJson("/admin/qams/subjects/{$subject->id}", [
                'school_class_id' => $class->id,
                'name'            => 'New Subject',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Subject']);
    }

    // ─────────────────────────────────────────────
    // Requirement #4 — Register teacher
    // ─────────────────────────────────────────────

    public function test_admin_can_register_teacher_with_profile(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/qams/teachers', [
                'name'        => 'John Teacher',
                'user_name'   => 'jteacher',
                'password'    => 'password',
                'job_history' => '5 years in high school',
                'education'   => 'MSc Mathematics',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', ['user_name' => 'jteacher', 'role' => 'teacher']);
        $this->assertDatabaseHas('teacher_profiles', [
            'job_history' => '5 years in high school',
            'education'   => 'MSc Mathematics',
        ]);
    }

    public function test_admin_cannot_register_teacher_with_duplicate_username(): void
    {
        User::factory()->create(['user_name' => 'taken']);

        $this->actingAs($this->admin)
            ->postJson('/admin/qams/teachers', [
                'name'      => 'Another',
                'user_name' => 'taken',
                'password'  => 'password',
            ])
            ->assertUnprocessable();
    }

    // ─────────────────────────────────────────────
    // Requirement #3 — Register student
    // ─────────────────────────────────────────────

    public function test_admin_can_register_student_with_full_details(): void
    {
        $class   = SchoolClass::create(['name' => 'Class 8']);
        $subject = Subject::create(['name' => 'Physics', 'school_class_id' => $class->id]);

        $this->actingAs($this->admin)
            ->postJson('/admin/qams/students', [
                'name'             => 'Jane Student',
                'user_name'        => 'jstudent',
                'password'         => 'password',
                'admission_number' => 'ADM-2026-001',
                'father_name'      => 'Robert Student',
                'school_class_id'  => $class->id,
                'subject_ids'      => [$subject->id],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', ['user_name' => 'jstudent', 'role' => 'student']);
        $this->assertDatabaseHas('student_profiles', [
            'admission_number' => 'ADM-2026-001',
            'father_name'      => 'Robert Student',
        ]);

        $student = User::where('user_name', 'jstudent')->first();
        $this->assertTrue($student->enrolledSubjects->contains($subject));
    }

    public function test_admission_number_must_be_unique(): void
    {
        $class = SchoolClass::create(['name' => 'Class 8']);
        $s1    = User::factory()->create(['role' => 'student']);
        StudentProfile::create([
            'user_id'          => $s1->id,
            'admission_number' => 'ADM-001',
            'father_name'      => 'Dad',
            'school_class_id'  => $class->id,
        ]);

        $this->actingAs($this->admin)
            ->postJson('/admin/qams/students', [
                'name'             => 'New Student',
                'user_name'        => 'newstudent',
                'password'         => 'password',
                'admission_number' => 'ADM-001',
                'father_name'      => 'Dad 2',
                'school_class_id'  => $class->id,
            ])
            ->assertUnprocessable();
    }

    // ─────────────────────────────────────────────
    // Requirement #5 — Update student
    // ─────────────────────────────────────────────

    public function test_admin_can_update_student_info(): void
    {
        $class   = SchoolClass::create(['name' => 'Class 7']);
        $student = User::factory()->create(['role' => 'student']);
        StudentProfile::create([
            'user_id'          => $student->id,
            'admission_number' => 'ADM-999',
            'father_name'      => 'Old Father',
            'school_class_id'  => $class->id,
        ]);

        $this->actingAs($this->admin)
            ->putJson("/admin/qams/students/{$student->id}", [
                'name'             => 'Updated Name',
                'user_name'        => $student->user_name,
                'admission_number' => 'ADM-999',
                'father_name'      => 'New Father',
                'school_class_id'  => $class->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('student_profiles', ['father_name' => 'New Father']);
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    // ─────────────────────────────────────────────
    // Requirement #5 — Update teacher
    // ─────────────────────────────────────────────

    public function test_admin_can_update_teacher_info(): void
    {
        $teacher = User::factory()->teacher()->create();
        TeacherProfile::create([
            'user_id'     => $teacher->id,
            'job_history' => 'Old history',
            'education'   => 'BSc',
        ]);

        $this->actingAs($this->admin)
            ->putJson("/admin/qams/teachers/{$teacher->id}", [
                'name'        => 'Updated Teacher',
                'user_name'   => $teacher->user_name,
                'job_history' => 'Updated history',
                'education'   => 'PhD',
            ])
            ->assertOk();

        $this->assertDatabaseHas('teacher_profiles', ['education' => 'PhD']);
    }

    // ─────────────────────────────────────────────
    // Requirement #6 — Assign subject to teacher
    // ─────────────────────────────────────────────

    public function test_admin_can_assign_subject_to_teacher(): void
    {
        $class   = SchoolClass::create(['name' => 'Class 6']);
        $subject = Subject::create(['name' => 'Chemistry', 'school_class_id' => $class->id]);
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)
            ->postJson("/admin/qams/subjects/{$subject->id}/assign-teacher", [
                'teacher_id' => $teacher->id,
            ])
            ->assertOk();

        $this->assertTrue($subject->teachers()->whereKey($teacher->id)->exists());
    }

    public function test_admin_cannot_assign_a_student_as_teacher_to_subject(): void
    {
        $class   = SchoolClass::create(['name' => 'Class 6']);
        $subject = Subject::create(['name' => 'Biology', 'school_class_id' => $class->id]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)
            ->postJson("/admin/qams/subjects/{$subject->id}/assign-teacher", [
                'teacher_id' => $student->id,
            ])
            ->assertUnprocessable();
    }

    // ─────────────────────────────────────────────
    // Requirement #7 — Block / Unblock
    // ─────────────────────────────────────────────

    public function test_admin_can_block_a_user(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)
            ->post("/admin/users/{$user->id}/toggle-block")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'active' => '0']);
    }

    public function test_admin_can_unblock_a_blocked_user(): void
    {
        $user = User::factory()->create(['role' => 'teacher', 'active' => '0']);

        $this->actingAs($this->admin)
            ->post("/admin/users/{$user->id}/toggle-block")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'active' => '1']);
    }

    public function test_admin_cannot_block_their_own_account(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/users/{$this->admin->id}/toggle-block")
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'active' => '1']);
    }

    // ─────────────────────────────────────────────
    // Requirement #8 — Reports
    // ─────────────────────────────────────────────

    public function test_admin_reports_return_correct_counts(): void
    {
        $class = SchoolClass::create(['name' => 'Class 5']);
        Subject::create(['name' => 'Art', 'school_class_id' => $class->id]);
        User::factory()->teacher()->create();
        User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)
            ->getJson('/admin/qams/reports')
            ->assertOk()
            ->assertJson([
                'students_count' => 1,
                'teachers_count' => 1,
                'subjects_count' => 1,
                'classes_count'  => 1,
            ]);
    }

    // ─────────────────────────────────────────────
    // Bug 2 regression — search must cover all roles
    // ─────────────────────────────────────────────

    public function test_admin_search_finds_teachers_and_admins_not_only_students(): void
    {
        $teacher = User::factory()->teacher()->create(['name' => 'Unique Teacher Name XYZ']);
        User::factory()->create(['role' => 'student', 'name' => 'Unrelated Student']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard?search=Unique+Teacher+Name+XYZ');

        $response->assertOk();
        $response->assertSee($teacher->name);
    }

    public function test_admin_search_without_term_returns_all_users(): void
    {
        User::factory()->teacher()->create(['name' => 'Some Teacher']);
        User::factory()->create(['role' => 'student', 'name' => 'Some Student']);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Some Teacher');
        $response->assertSee('Some Student');
    }

    // ─────────────────────────────────────────────
    // Non-admin cannot access admin QAMS routes
    // ─────────────────────────────────────────────

    public function test_teacher_cannot_access_admin_qams_routes(): void
    {
        $teacher = User::factory()->teacher()->create();
        $this->actingAs($teacher)->get('/admin/qams/classes')->assertForbidden();
    }

    public function test_student_cannot_access_admin_qams_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get('/admin/qams/students')->assertForbidden();
    }
}
