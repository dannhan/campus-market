<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailActionBarTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $seller;

    protected User $buyer;

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

        $this->category = Category::create(['name' => 'Buku per Semester', 'slug' => 'buku-per-semester']);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Struktur Data Edisi Revisi',
            'slug' => 'buku-struktur-data-edisi-revisi',
            'description' => 'Buku bekas layak pakai.',
            'price' => 100000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'bargainable' => true,
        ]);
    }

    private function createVerifiedStudentUser(string $nim, string $name, string $email): User
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
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_sees_login_cta_in_bottom_bar_without_offer_form(): void
    {
        $this->get(route('products.show', $this->product))
            ->assertOk()
            ->assertSee('Masuk untuk menawar')
            ->assertSee(route('login'))
            ->assertDontSee('Buat Penawaran')
            ->assertDontSee('aria-labelledby="offer-sheet-title"', false)
            ->assertDontSee(route('offers.store', $this->product));
    }

    public function test_buyer_sees_offer_cta_and_bottom_sheet_form(): void
    {
        $this->actingAs($this->buyer)
            ->get(route('products.show', $this->product))
            ->assertOk()
            ->assertSee('Buat Penawaran')
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('aria-labelledby="offer-sheet-title"', false)
            ->assertSee('aria-label="Tutup"', false)
            ->assertSee('action="'.route('offers.store', $this->product).'"', false)
            ->assertSee('name="offered_price"', false)
            ->assertSee('name="message"', false)
            ->assertSee(route('products.chat', $this->product))
            ->assertSee('Kirim Tawaran')
            ->assertSee('Penjual akan menerima penawaran Anda.');
    }

    public function test_buyer_with_pending_offer_sees_inline_status_without_offer_cta(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 80000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->buyer)
            ->get(route('products.show', $this->product))
            ->assertOk()
            ->assertSee('Penawaran Anda sedang menunggu respons penjual')
            ->assertSee($offer->formatted_price)
            ->assertSee(route('offers.cancel', $offer))
            ->assertSee(route('products.chat', $this->product))
            ->assertDontSee('Buat Penawaran')
            ->assertDontSee('aria-labelledby="offer-sheet-title"', false)
            ->assertDontSee(route('offers.store', $this->product));
    }

    public function test_validation_errors_reopen_the_offer_sheet_with_old_input(): void
    {
        $this->actingAs($this->buyer)
            ->from(route('products.show', $this->product))
            ->followingRedirects()
            ->post(route('offers.store', $this->product), ['offered_price' => 500])
            ->assertOk()
            ->assertSee('offerSheetOpen: true', false)
            ->assertSee('value="500"', false)
            ->assertSee('Harga penawaran minimal Rp 1.000.');
    }

    public function test_non_bargainable_product_hides_offer_cta(): void
    {
        $this->product->update(['bargainable' => false]);

        $this->actingAs($this->buyer)
            ->get(route('products.show', $this->product))
            ->assertOk()
            ->assertSee('Penjual tidak menerima tawaran harga untuk produk ini.')
            ->assertSee(route('products.chat', $this->product))
            ->assertDontSee('Buat Penawaran')
            ->assertDontSee('aria-labelledby="offer-sheet-title"', false)
            ->assertDontSee(route('offers.store', $this->product));
    }

    public function test_owner_does_not_see_buyer_action_bar_or_offer_cta(): void
    {
        $this->actingAs($this->seller)
            ->get(route('products.show', $this->product))
            ->assertOk()
            ->assertSee('Edit Produk')
            ->assertSee('Hapus Produk')
            ->assertDontSee('Masuk untuk menawar')
            ->assertDontSee('Buat Penawaran')
            ->assertDontSee(route('products.chat', $this->product))
            ->assertDontSee('aria-labelledby="offer-sheet-title"', false)
            ->assertDontSee(route('offers.store', $this->product));
    }
}
