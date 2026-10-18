<?php

namespace Tests\Feature\Offer;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BargainingTest extends TestCase
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

    protected function createVerifiedStudentUser(string $nim, string $name, string $email): User
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

    public function test_buyer_can_make_an_offer(): void
    {
        $response = $this->actingAs($this->buyer)->post(route('offers.store', $this->product), [
            'offered_price' => 80000,
            'message' => 'Boleh nego kak?',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $offer = Offer::where('product_id', $this->product->id)->where('user_id', $this->buyer->id)->first();
        $this->assertNotNull($offer);
        $this->assertSame('pending', $offer->status);
        $this->assertEquals(80000, $offer->offered_price);
        $this->assertNull($offer->parent_offer_id);
    }

    public function test_seller_cannot_offer_on_own_product(): void
    {
        $response = $this->actingAs($this->seller)->post(route('offers.store', $this->product), [
            'offered_price' => 80000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menawar produk milik Anda sendiri.');
        $this->assertSame(0, Offer::count());
    }

    public function test_buyer_cannot_duplicate_pending_offer_on_same_product(): void
    {
        Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 70000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->buyer)->post(route('offers.store', $this->product), [
            'offered_price' => 80000,
        ]);

        $response->assertSessionHas('error', 'Anda masih memiliki penawaran yang menunggu respons penjual di produk ini.');
        $this->assertSame(1, Offer::count());
    }

    public function test_buyer_can_offer_again_after_previous_offer_was_rejected(): void
    {
        Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 70000,
            'status' => 'rejected',
        ]);

        $this->actingAs($this->buyer)->post(route('offers.store', $this->product), [
            'offered_price' => 85000,
        ])->assertSessionHas('status');

        $this->assertSame(2, Offer::count());
    }

    public function test_non_bargainable_product_rejects_offers(): void
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Harga Pas',
            'slug' => 'harga-pas',
            'description' => 'Tidak menerima nego.',
            'price' => 50000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'bargainable' => false,
        ]);

        $this->actingAs($this->buyer)
            ->post(route('offers.store', $product), ['offered_price' => 40000])
            ->assertSessionHas('error', 'Penjual tidak menerima tawaran harga untuk produk ini.');

        $this->assertSame(0, Offer::count());
    }

    public function test_seller_can_accept_offer_and_product_becomes_sold(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 90000,
            'status' => 'pending',
        ]);

        Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->otherBuyer->id,
            'offered_price' => 95000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->seller)->post(route('offers.respond', $offer), [
            'action' => 'accept',
        ]);

        $response->assertRedirect(route('products.show', $this->product));
        $response->assertSessionHas('status');

        $this->assertSame('accepted', $offer->fresh()->status);
        $this->assertSame('sold', $this->product->fresh()->status);
        $this->assertSame('rejected', Offer::where('user_id', $this->otherBuyer->id)->first()->fresh()->status);
    }

    public function test_buyer_cannot_accept_their_own_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 90000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('offers.respond', $offer), ['action' => 'accept'])
            ->assertSessionHas('error', 'Hanya penjual yang dapat menerima penawaran.');

        $this->assertSame('pending', $offer->fresh()->status);
        $this->assertSame('active', $this->product->fresh()->status);
    }

    public function test_seller_can_reject_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 50000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->seller)
            ->post(route('offers.respond', $offer), ['action' => 'reject'])
            ->assertSessionHas('status');

        $this->assertSame('rejected', $offer->fresh()->status);
        $this->assertSame('active', $this->product->fresh()->status);
    }

    public function test_buyer_cannot_reject_their_own_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 50000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('offers.respond', $offer), ['action' => 'reject'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $offer->fresh()->status);
    }

    public function test_seller_can_counter_and_thread_is_created(): void
    {
        $rootOffer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->seller)->post(route('offers.respond', $rootOffer), [
            'action' => 'counter',
            'offered_price' => 85000,
        ]);

        $response->assertRedirect(route('offers.index'));

        $counter = Offer::where('parent_offer_id', $rootOffer->id)->first();
        $this->assertNotNull($counter);
        $this->assertSame('pending', $counter->status);
        $this->assertEquals(85000, $counter->offered_price);
        $this->assertSame($this->seller->id, $counter->user_id);
        $this->assertSame('countered', $rootOffer->fresh()->status);
    }

    public function test_buyer_can_counter_back_and_then_seller_accepts(): void
    {
        $rootOffer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'countered',
        ]);

        $sellerCounter = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->seller->id,
            'parent_offer_id' => $rootOffer->id,
            'offered_price' => 85000,
            'status' => 'pending',
        ]);

        // Buyer counters the seller's counter
        $this->actingAs($this->buyer)
            ->post(route('offers.respond', $sellerCounter), ['action' => 'counter', 'offered_price' => 80000])
            ->assertSessionHas('status');

        $buyerCounter = Offer::where('parent_offer_id', $sellerCounter->id)->first();
        $this->assertNotNull($buyerCounter);
        $this->assertEquals(80000, $buyerCounter->offered_price);

        // Seller accepts the buyer's final counter
        $this->actingAs($this->seller)
            ->post(route('offers.respond', $buyerCounter), ['action' => 'accept'])
            ->assertSessionHas('status');

        $this->assertSame('accepted', $buyerCounter->fresh()->status);
        $this->assertSame('sold', $this->product->fresh()->status);
    }

    public function test_outsider_cannot_respond_to_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->otherBuyer)
            ->post(route('offers.respond', $offer), ['action' => 'accept'])
            ->assertSessionHas('error', 'Anda tidak berhak merespons penawaran ini.');

        $this->assertSame('pending', $offer->fresh()->status);
    }

    public function test_responding_requires_a_valid_action(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->seller)
            ->post(route('offers.respond', $offer), ['action' => 'explode'])
            ->assertSessionHasErrors('action');
    }

    public function test_counter_response_requires_price(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->seller)
            ->post(route('offers.respond', $offer), ['action' => 'counter'])
            ->assertSessionHasErrors('offered_price');
    }

    public function test_offerer_can_withdraw_pending_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('offers.cancel', $offer))
            ->assertSessionHas('status');

        $this->assertSame('withdrawn', $offer->fresh()->status);
    }

    public function test_non_offerer_cannot_withdraw_offer(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->seller)
            ->post(route('offers.cancel', $offer))
            ->assertSessionHas('error', 'Anda hanya dapat membatalkan penawaran milik Anda sendiri.');

        $this->assertSame('pending', $offer->fresh()->status);
    }

    public function test_offers_inbox_lists_received_and_sent_offers(): void
    {
        Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 60000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->seller)
            ->get(route('offers.index'))
            ->assertOk()
            ->assertSee('Pembeli Kedua')
            ->assertSee('Buku Struktur Data Edisi Revisi');

        $this->actingAs($this->buyer)
            ->get(route('offers.index'))
            ->assertOk()
            ->assertSee('Penjual Pertama');
    }

    public function test_offers_on_sold_product_are_rejected(): void
    {
        $this->product->update(['status' => 'sold']);

        $this->actingAs($this->buyer)
            ->post(route('offers.store', $this->product), ['offered_price' => 80000])
            ->assertSessionHas('error', 'Produk ini tidak lagi menerima penawaran.');

        $this->assertSame(0, Offer::count());
    }
}
