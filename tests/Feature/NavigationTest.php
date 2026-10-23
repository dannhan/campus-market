<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $verifiedUser;

    protected User $unverifiedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->verifiedUser = $this->createStudentUser('24060122130001', 'Budi Santoso', 'budi.santoso@students.undip.ac.id', true);
        $this->unverifiedUser = $this->createStudentUser('24060122130002', 'Sari Wulandari', 'sari.wulandari@students.undip.ac.id', false);
    }

    protected function createStudentUser(string $nim, string $name, string $email, bool $isVerified): User
    {
        $student = Student::create([
            'university_id' => $this->university->id,
            'student_number' => $nim,
            'full_name' => $name,
            'major' => 'Informatika',
            'university_email' => $email,
            'is_active' => true,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('Password123!'),
            'student_id' => $student->id,
            'email_verified_at' => $isVerified ? now() : null,
        ]);
    }

    public function test_verified_user_sees_all_five_navigation_tabs(): void
    {
        $response = $this->actingAs($this->verifiedUser)->get('/products');

        $response->assertOk();

        $navigation = $this->bottomNavigation($response->getContent());

        $this->assertStringContainsString('Beranda', $navigation);
        $this->assertStringContainsString('Jelajahi', $navigation);
        $this->assertStringContainsString('Jual', $navigation);
        $this->assertStringContainsString('Chat', $navigation);
        $this->assertStringContainsString('Saya', $navigation);
    }

    public function test_navigation_has_accessible_label_and_one_current_page(): void
    {
        $response = $this->actingAs($this->verifiedUser)->get('/products');

        $response->assertOk()
            ->assertSee('aria-label="Navigasi utama"', false);

        $navigation = $this->bottomNavigation($response->getContent());

        $this->assertSame(1, substr_count($navigation, 'aria-current="page"'));
    }

    public function test_chat_tab_links_to_conversations_index(): void
    {
        $this->actingAs($this->verifiedUser)
            ->get('/products')
            ->assertOk()
            ->assertSee('href="'.route('chats.index').'"', false);
    }

    public function test_guests_and_unverified_users_do_not_see_bottom_navigation(): void
    {
        $this->get('/products')
            ->assertOk()
            ->assertDontSee('aria-label="Navigasi utama"', false);

        $this->actingAs($this->unverifiedUser)
            ->get('/products')
            ->assertOk()
            ->assertDontSee('aria-label="Navigasi utama"', false);
    }

    /**
     * Extract the bottom navigation markup so assertions ignore the rest of the page.
     */
    protected function bottomNavigation(string $html): string
    {
        preg_match('/<nav\b[^>]*aria-label="Navigasi utama"[^>]*>.*?<\/nav>/s', $html, $matches);

        $this->assertNotEmpty($matches, 'Bottom navigation was not rendered.');

        return $matches[0];
    }
}
