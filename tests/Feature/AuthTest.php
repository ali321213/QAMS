<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auth tests — covers Admin req #1, Teacher req #1, Student req #1
 *
 * Tests:
 *  - Guest is redirected to login for every protected page
 *  - Any user can register (public form)
 *  - Correct credentials log user in
 *  - Wrong password is rejected
 *  - Blocked account cannot log in
 *  - Admin is redirected to admin dashboard after login
 *  - Teacher is redirected to teacher dashboard after login
 *  - Student is redirected to student dashboard after login
 *  - User can log out
 *  - Blocked user is kicked out mid-session
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    // Guest access
    // ─────────────────────────────────────────────

    public function test_root_url_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    /** @dataProvider protectedRouteProvider */
    public function test_guest_is_redirected_to_login_for_protected_routes(string $url): void
    {
        $this->get($url)->assertRedirect(route('login'));
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'dashboard'           => ['/dashboard'],
            'admin dashboard'     => ['/admin/dashboard'],
            'admin qams hub'      => ['/admin/qams/'],
            'teacher dashboard'   => ['/teacher/dashboard'],
            'student dashboard'   => ['/student/dashboard'],
        ];
    }

    // ─────────────────────────────────────────────
    // Registration
    // ─────────────────────────────────────────────

    public function test_user_can_register_via_form(): void
    {
        $this->post('/register', [
            'name'                  => 'New User',
            'user_name'             => 'newuser',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role'                  => 'student',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['user_name' => 'newuser', 'role' => 'student']);
    }

    public function test_registration_fails_with_duplicate_username(): void
    {
        User::factory()->create(['user_name' => 'taken']);

        $this->post('/register', [
            'name'                  => 'Another',
            'user_name'             => 'taken',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role'                  => 'student',
        ])->assertSessionHasErrors('user_name');
    }

    // ─────────────────────────────────────────────
    // Login
    // ─────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['user_name' => 'testuser']);

        $this->post('/login', [
            'user_name' => 'testuser',
            'password'  => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['user_name' => 'testuser']);

        $this->post('/login', [
            'user_name' => 'testuser',
            'password'  => 'wrong-password',
        ])->assertSessionHasErrors('user_name');

        $this->assertGuest();
    }

    public function test_login_fails_for_non_existent_username(): void
    {
        $this->post('/login', [
            'user_name' => 'nobody',
            'password'  => 'password',
        ])->assertSessionHasErrors('user_name');
    }

    public function test_blocked_account_cannot_login(): void
    {
        User::factory()->create([
            'user_name' => 'blocked',
            'active'    => '0',
        ]);

        $this->post('/login', [
            'user_name' => 'blocked',
            'password'  => 'password',
        ])->assertSessionHasErrors('user_name');

        $this->assertGuest();
    }

    // ─────────────────────────────────────────────
    // Role-based redirects after login
    // ─────────────────────────────────────────────

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_teacher_is_redirected_to_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();
        $this->actingAs($teacher)->get('/dashboard')
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_student_is_redirected_to_student_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get('/dashboard')
            ->assertRedirect(route('student.dashboard'));
    }

    // ─────────────────────────────────────────────
    // Logout
    // ─────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ─────────────────────────────────────────────
    // Mid-session block
    // ─────────────────────────────────────────────

    public function test_blocked_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        // Admin blocks the user
        $user->update(['active' => '0']);

        // On next request the middleware should force logout
        $this->actingAs($user)->get('/student/dashboard')
            ->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────
    // Cross-role access denied
    // ─────────────────────────────────────────────

    public function test_student_cannot_access_teacher_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get('/teacher/dashboard')->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_dashboard(): void
    {
        // EnsureUserIsAdmin middleware redirects non-admins to /dashboard (not 403)
        $teacher = User::factory()->teacher()->create();
        $this->actingAs($teacher)->get('/admin/dashboard')
            ->assertRedirect(route('dashboard'));
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        // EnsureUserIsAdmin middleware redirects non-admins to /dashboard (not 403)
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get('/admin/dashboard')
            ->assertRedirect(route('dashboard'));
    }
}
