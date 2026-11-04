<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $studentUser;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Administrator CampusMarket',
            'email' => 'admin@campusmarket.ac.id',
            'email_verified_at' => now(),
        ]);

        $university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $student = Student::create([
            'university_id' => $university->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->forStudent($student)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Buku per Semester',
            'slug' => 'buku-per-semester',
            'icon' => 'book-open',
            'description' => 'Buku teks kuliah dan bahan ajar.',
            'sort_order' => 1,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/categories');
        $response->assertRedirect(route('login'));

        $createResponse = $this->get('/admin/categories/create');
        $createResponse->assertRedirect(route('login'));
    }

    public function test_non_admin_student_user_is_forbidden_from_admin_categories(): void
    {
        $response = $this->actingAs($this->studentUser)->get('/admin/categories');
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->studentUser)->get('/admin/categories/create');
        $createResponse->assertStatus(403);

        $storeResponse = $this->actingAs($this->studentUser)->post('/admin/categories', [
            'name' => 'Kategori Baru',
        ]);
        $storeResponse->assertStatus(403);

        $editResponse = $this->actingAs($this->studentUser)->get("/admin/categories/{$this->category->id}/edit");
        $editResponse->assertStatus(403);

        $updateResponse = $this->actingAs($this->studentUser)->put("/admin/categories/{$this->category->id}", [
            'name' => 'Nama Baru',
            'slug' => 'nama-baru',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($this->studentUser)->delete("/admin/categories/{$this->category->id}");
        $deleteResponse->assertStatus(403);
    }

    public function test_admin_can_view_category_index_screen(): void
    {
        $response = $this->actingAs($this->admin)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Administrator Panel');
        $response->assertSee('Kelola Kategori Produk');

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Kelola Kategori');
        $response->assertSee('Buku per Semester');
        $response->assertSee('buku-per-semester');
        $response->assertSee('+ Tambah');
    }

    public function test_admin_can_view_create_category_screen(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertStatus(200);
        $response->assertSee('Tambah Kategori');
        $response->assertSee('Nama Kategori');
        $response->assertSee('Slug URL');
    }

    public function test_admin_can_create_category_with_auto_generated_slug(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Perlengkapan Robotika',
            'slug' => '',
            'icon' => 'cpu',
            'description' => 'Komponen kit Arduino, sensor, dan modul robotika.',
            'sort_order' => 9,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('categories', [
            'name' => 'Perlengkapan Robotika',
            'slug' => 'perlengkapan-robotika',
            'icon' => 'cpu',
            'sort_order' => 9,
        ]);
    }

    public function test_admin_can_create_category_with_custom_slug(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Alat Musik & Band Kampus',
            'slug' => 'musik-kampus',
            'icon' => 'briefcase',
            'description' => 'Gitar, bass, drum pad, dan instrumen mahasiswa.',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Alat Musik & Band Kampus',
            'slug' => 'musik-kampus',
        ]);
    }

    public function test_category_creation_fails_with_duplicate_name_or_slug(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Buku per Semester',
            'slug' => 'buku-per-semester',
        ]);

        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_admin_can_view_edit_category_screen(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/categories/{$this->category->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Kategori');
        $response->assertSee($this->category->name);
        $response->assertSee($this->category->slug);
    }

    public function test_admin_can_update_category_successfully(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/categories/{$this->category->id}", [
            'name' => 'Buku Kuliah & E-Book',
            'slug' => 'buku-kuliah-dan-ebook',
            'icon' => 'book-open',
            'description' => 'Koleksi buku cetak dan e-book kuliah resmi.',
            'sort_order' => 2,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');

        $this->category->refresh();
        $this->assertEquals('Buku Kuliah & E-Book', $this->category->name);
        $this->assertEquals('buku-kuliah-dan-ebook', $this->category->slug);
        $this->assertEquals(2, $this->category->sort_order);
    }

    public function test_admin_can_delete_category_when_no_products_exist(): void
    {
        $categoryToDelete = Category::create([
            'name' => 'Kategori Sementara',
            'slug' => 'kategori-sementara',
            'icon' => 'pencil',
            'sort_order' => 99,
        ]);

        $this->assertDatabaseHas('categories', ['id' => $categoryToDelete->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$categoryToDelete->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('categories', ['id' => $categoryToDelete->id]);
    }

    public function test_admin_cannot_delete_category_that_has_attached_products(): void
    {
        // Create product belonging to this category
        Product::create([
            'user_id' => $this->studentUser->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Kalkulus Purcel Edisi 9',
            'slug' => 'buku-kalkulus-purcel-edisi-9',
            'description' => 'Buku kalkulus edisi 9 masih sangat mulus.',
            'price' => 75000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Tembalang',
            'bargainable' => true,
        ]);

        $this->assertEquals(1, $this->category->products()->count());

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$this->category->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }
}
