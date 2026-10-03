<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected University $undip;
    protected University $ugm;
    protected Student $undipStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undip = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->ugm = University::create([
            'name' => 'Universitas Gadjah Mada',
            'slug' => 'ugm',
            'email_domain' => 'mail.ugm.ac.id',
        ]);

        $this->undipStudent = Student::create([
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Mahasiswa');
        $response->assertSee('Universitas Diponegoro');
    }

    public function test_valid_student_can_register(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@students.undip.ac.id',
            'student_id' => $this->undipStudent->id,
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'university_email' => 'not-a-valid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['university_email']);
        $this->assertGuest();
    }

    public function test_unsupported_university_domain_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'university_email' => 'budi@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['university_email']);
        $this->assertGuest();
    }

    public function test_student_number_not_found_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Mahasiswa Palsu',
            'university_id' => $this->undip->id,
            'student_number' => '999999999999',
            'university_email' => 'palsu@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['student_number']);
        $this->assertGuest();
    }

    public function test_student_belongs_to_another_university_is_rejected(): void
    {
        // Student exists in UGM
        $ugmStudent = Student::create([
            'university_id' => $this->ugm->id,
            'student_number' => '22/498765/TK/99999',
            'full_name' => 'Dewi Lestari',
            'major' => 'Teknik Industri',
            'university_email' => 'dewi@mail.ugm.ac.id',
            'is_active' => true,
        ]);

        // Attempting to register under UNDIP with UGM student number
        $response = $this->post('/register', [
            'name' => 'Dewi Lestari',
            'university_id' => $this->undip->id,
            'student_number' => '22/498765/TK/99999',
            'university_email' => 'dewi@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['student_number']);
        $this->assertGuest();
    }

    public function test_duplicate_email_is_rejected(): void
    {
        // First create user for this student
        User::create([
            'name' => $this->undipStudent->full_name,
            'email' => $this->undipStudent->university_email,
            'password' => bcrypt('Password123!'),
            'student_id' => $this->undipStudent->id,
        ]);

        // Second attempt with duplicate email
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['university_email']);
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_terms_agreement_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            // 'terms' omitted
        ]);

        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
    }
}
