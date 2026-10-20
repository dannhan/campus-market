<?php

namespace Tests\Feature\Profile;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_authenticated_verified_user_can_view_profile(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Universitas Diponegoro');
        $response->assertSee('Informatika');
        $response->assertSee('24060122130001');
        $response->assertSee('budi.santoso@students.undip.ac.id');
        $response->assertSee('Mahasiswa Terverifikasi');
        $response->assertSee('Belum ada rating');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $secondStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130002',
            'full_name' => 'Siti Rahmawati',
            'major' => 'Sistem Informasi',
            'university_email' => 'siti.rahmawati@students.undip.ac.id',
            'is_active' => true,
        ]);

        $unverifiedUser = User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@students.undip.ac.id',
            'password' => bcrypt('Password123!'),
            'student_id' => $secondStudent->id,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverifiedUser)->get('/profile');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_profile_edit_screen_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('Edit Profil');
        $response->assertSee('Data Kemahasiswaan Terkunci');
        $response->assertSee('24060122130001');
    }

    public function test_profile_edit_form_submits_avatar_natively_without_turbo(): void
    {
        $response = $this->actingAs($this->user)->get('/profile/edit');

        $response->assertOk();
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('data-turbo="false"', false);
    }

    public function test_user_can_update_allowed_fields_name_and_avatar(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('profile.jpg', 200, 200);

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Budi Santoso S.Kom',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $this->user->refresh();
        $this->assertEquals('Budi Santoso S.Kom', $this->user->name);
        $this->assertNotNull($this->user->avatar);
        Storage::disk('public')->assertExists($this->user->avatar);
    }

    public function test_avatar_larger_than_two_megabytes_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Budi Santoso',
            'avatar' => UploadedFile::fake()->image('avatar-besar.jpg')->size(3072),
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($this->user->fresh()->avatar);
    }

    public function test_protected_identity_fields_cannot_be_arbitrarily_changed(): void
    {
        $originalEmail = $this->user->email;
        $originalStudentId = $this->user->student_id;

        // An attacker tries sending new email, student_id, or student_number via patch
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Budi New Name',
            'email' => 'hacked@evil.com',
            'student_id' => 9999,
            'student_number' => '1111111111',
            'university_id' => 999,
        ]);

        $response->assertRedirect(route('profile'));

        $this->user->refresh();
        $this->assertEquals('Budi New Name', $this->user->name);
        // Protected fields remain untouched:
        $this->assertEquals($originalEmail, $this->user->email);
        $this->assertEquals($originalStudentId, $this->user->student_id);
        $this->assertEquals('24060122130001', $this->user->student->student_number);
    }

    public function test_user_only_updates_their_own_authenticated_account(): void
    {
        $secondStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130003',
            'full_name' => 'Ahmad Pratama',
            'major' => 'Teknik Elektro',
            'university_email' => 'ahmad.pratama@students.undip.ac.id',
            'is_active' => true,
        ]);

        $secondUser = User::create([
            'name' => 'Ahmad Pratama',
            'email' => 'ahmad.pratama@students.undip.ac.id',
            'password' => bcrypt('Password123!'),
            'student_id' => $secondStudent->id,
            'email_verified_at' => now(),
        ]);

        // User 1 updates profile
        $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Budi Self Update',
        ]);

        $this->user->refresh();
        $secondUser->refresh();

        $this->assertEquals('Budi Self Update', $this->user->name);
        $this->assertEquals('Ahmad Pratama', $secondUser->name);
    }
}
