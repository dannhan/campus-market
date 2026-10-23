<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_renders_category_title_price_condition_and_location(): void
    {
        $university = $this->createUniversity();
        $seller = $this->createStudentUser($university, '24060122130001', 'Budi Santoso', 'budi.santoso@students.undip.ac.id');
        $category = Category::create(['name' => 'Buku per Semester', 'slug' => 'buku-per-semester']);
        $this->createProduct($seller, $category, [
            'title' => 'Buku Struktur Data Edisi Revisi',
            'price' => 75000,
            'condition' => 'used',
            'location' => 'Kampus Tembalang',
        ]);

        $html = $this->renderProductCards();

        $this->assertStringContainsString('Buku per Semester', $html);
        $this->assertStringContainsString('Buku Struktur Data Edisi Revisi', $html);
        $this->assertStringContainsString('Rp 75.000', $html);
        $this->assertStringContainsString('Bekas', $html);
        $this->assertStringContainsString('Kampus Tembalang', $html);
        $this->assertStringContainsString('M17.657 16.657', $html);
    }

    public function test_card_falls_back_to_seller_university_when_location_is_empty(): void
    {
        $university = $this->createUniversity();
        $seller = $this->createStudentUser($university, '24060122130001', 'Budi Santoso', 'budi.santoso@students.undip.ac.id');
        $category = Category::create(['name' => 'Buku per Semester', 'slug' => 'buku-per-semester']);
        $this->createProduct($seller, $category, [
            'title' => 'Buku Struktur Data Edisi Revisi',
            'location' => null,
        ]);

        $html = $this->renderProductCards();

        $this->assertStringContainsString('Universitas Diponegoro', $html);
        $this->assertStringNotContainsString('Mahasiswa', $html);
    }

    public function test_card_renders_wishlist_toggle_for_authenticated_non_owner(): void
    {
        $university = $this->createUniversity();
        $seller = $this->createStudentUser($university, '24060122130001', 'Budi Santoso', 'budi.santoso@students.undip.ac.id');
        $buyer = $this->createStudentUser($university, '24060122130002', 'Sari Wulandari', 'sari.wulandari@students.undip.ac.id');
        $category = Category::create(['name' => 'Buku per Semester', 'slug' => 'buku-per-semester']);
        $product = $this->createProduct($seller, $category, [
            'title' => 'Buku Struktur Data Edisi Revisi',
        ]);

        $html = $this->actingAs($buyer)->renderProductCards();

        $this->assertStringContainsString('action="'.route('products.wishlist.toggle', $product).'"', $html);
        $this->assertStringContainsString('Simpan ke Wishlist', $html);
    }

    private function createUniversity(): University
    {
        return University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);
    }

    private function createStudentUser(University $university, string $studentNumber, string $name, string $email): User
    {
        $student = Student::create([
            'university_id' => $university->id,
            'student_number' => $studentNumber,
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
            'email_verified_at' => now(),
        ]);
    }

    private function createProduct(User $seller, Category $category, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Produk',
            'slug' => Product::generateUniqueSlug('Produk '.uniqid()),
            'description' => 'Deskripsi produk.',
            'price' => 50000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
        ], $overrides));
    }

    private function renderProductCards(): string
    {
        return $this->getJson('/products?fragment=1')
            ->assertOk()
            ->json('html');
    }
}
