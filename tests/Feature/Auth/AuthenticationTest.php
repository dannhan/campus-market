<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;
    protected Student $student;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->student = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@students.undip.ac.id',
            'password' => bcrypt('Password123!'),
            'student_id' => $this->student->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk');
        $response->assertSee('Email Universitas');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'WrongPassword!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_remember_me_functionality(): void
    {
        $response = $this->post('/login', [
            'email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
            'remember' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertCookieNotExpired(\Illuminate\Support\Facades\Auth::getRecallerName());
    }

    public function test_unauthenticated_user_cannot_access_protected_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    public function test_users_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
