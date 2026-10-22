<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $seller;

    protected User $buyer;

    protected User $otherBuyer;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->seller = $this->createVerifiedStudentUser('24060122130001', 'Penjual Pertama', 'penjual@students.undip.ac.id');
        $this->buyer = $this->createVerifiedStudentUser('24060122130002', 'Pembeli Kedua', 'pembeli@students.undip.ac.id');
        $this->otherBuyer = $this->createVerifiedStudentUser('24060122130003', 'Pembeli Ketiga', 'pembeli3@students.undip.ac.id');

        $this->category = Category::create(['name' => 'Buku Kuliah', 'slug' => 'buku-kuliah']);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Pemrograman Web Modern',
            'slug' => 'buku-pemrograman-web-modern',
            'description' => 'Buku bagus untuk kuliah.',
            'price' => 85000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'bargainable' => true,
        ]);
    }

    protected function createVerifiedStudentUser(string $nim, string $name, string $email, bool $verified = true): User
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
            'email_verified_at' => $verified ? now() : null,
        ]);
    }

    public function test_guest_cannot_access_wishlist_or_toggle_bookmark(): void
    {
        $this->get(route('wishlist.index'))
            ->assertRedirect(route('login'));

        $this->post(route('products.wishlist.toggle', $this->product))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_student_cannot_access_wishlist(): void
    {
        $unverified = $this->createVerifiedStudentUser('24060122130099', 'Belum Verif', 'unverified@students.undip.ac.id', false);

        $this->actingAs($unverified)
            ->get(route('wishlist.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_buyer_can_add_product_to_wishlist(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('products.wishlist.toggle', $this->product));

        $response->assertRedirect();
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
        $this->assertTrue($this->buyer->hasWishlisted($this->product));
    }

    public function test_buyer_can_remove_product_from_wishlist_via_toggle(): void
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('products.wishlist.toggle', $this->product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
        $this->assertFalse($this->buyer->hasWishlisted($this->product));
    }

    public function test_buyer_can_remove_product_from_wishlist_via_delete_route(): void
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->delete(route('wishlist.destroy', $this->product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_cannot_wishlist_own_product(): void
    {
        $response = $this->actingAs($this->seller)
            ->post(route('products.wishlist.toggle', $this->product));

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->seller->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_view_their_wishlist_page(): void
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->get(route('wishlist.index'));

        $response->assertOk();
        $response->assertSee('Buku Pemrograman Web Modern');
        $response->assertSee('Rp 85.000');
    }

    public function test_wishlist_only_shows_authenticated_users_saved_products(): void
    {
        Wishlist::create([
            'user_id' => $this->otherBuyer->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->get(route('wishlist.index'));

        $response->assertOk();
        $response->assertSee('Wishlist kamu masih kosong');
        $response->assertDontSee('Buku Pemrograman Web Modern');
    }

    public function test_toggle_wishlist_via_json_request_returns_json_status(): void
    {
        $response = $this->actingAs($this->buyer)
            ->postJson(route('products.wishlist.toggle', $this->product));

        $response->assertOk()
            ->assertJson([
                'wishlisted' => true,
                'message' => 'Produk berhasil ditambahkan ke wishlist.',
            ]);

        // Toggle again to remove
        $responseRemove = $this->actingAs($this->buyer)
            ->postJson(route('products.wishlist.toggle', $this->product));

        $responseRemove->assertOk()
            ->assertJson([
                'wishlisted' => false,
                'message' => 'Produk dihapus dari wishlist.',
            ]);
    }

    public function test_deleting_product_cascades_and_removes_from_wishlists(): void
    {
        Wishlist::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        $this->product->delete();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }
}
