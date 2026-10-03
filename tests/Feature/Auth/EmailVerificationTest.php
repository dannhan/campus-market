<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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
            'email_verified_at' => null, // unverified initially
        ]);
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Email');
        $response->assertSee($this->user->email);
    }

    public function test_email_can_be_verified(): void
    {
        Event::fake([Verified::class]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        $response = $this->actingAs($this->user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($this->user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('profile'));
    }

    public function test_already_verified_user_is_redirected_to_profile(): void
    {
        $this->user->markEmailAsVerified();

        $response = $this->actingAs($this->user)->get('/email/verify');
        $response->assertRedirect(route('profile'));

        // Attempting to visit verification link again
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        $verifyResponse = $this->actingAs($this->user)->get($verificationUrl);
        $verifyResponse->assertRedirect(route('profile'));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1('wrong-email@domain.com')]
        );

        $this->actingAs($this->user)->get($verificationUrl);

        $this->assertFalse($this->user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_cannot_access_profile_and_is_redirected_to_verification_notice(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verification_email_can_be_resent(): void
    {
        $response = $this->actingAs($this->user)->post('/email/verification-notification');

        $response->assertSessionHas('status', 'Tautan verifikasi baru telah dikirimkan ke alamat email universitas Anda.');
    }
}
