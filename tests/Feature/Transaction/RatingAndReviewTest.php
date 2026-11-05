<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingAndReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected User $otherUser;

    protected Product $product;

    protected Category $category;

    protected University $university;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'undip.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
        ]);

        $sellerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Seller',
            'major' => 'Informatika',
            'university_email' => 'budi@undip.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Budi Seller',
            'email' => 'budi@undip.ac.id',
            'student_id' => $sellerStudent->id,
            'student_status' => 'active',
        ]);

        $buyerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130002',
            'full_name' => 'Siti Buyer',
            'major' => 'Biologi',
            'university_email' => 'siti@undip.ac.id',
            'is_active' => true,
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Siti Buyer',
            'email' => 'siti@undip.ac.id',
            'student_id' => $buyerStudent->id,
            'student_status' => 'active',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Rian Stranger',
            'email' => 'rian@undip.ac.id',
        ]);

        $this->category = Category::create([
            'name' => 'Buku & Catatan Kuliah',
            'slug' => 'buku-catatan',
            'icon' => 'book',
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Kalkulus Purcel Edisi 9',
            'slug' => 'buku-kalkulus-purcel-edisi-9',
            'description' => 'Buku masih mulus tanpa coretan pensil.',
            'price' => 75000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'location' => 'Kampus Pleburan Undip',
            'status' => 'sold',
        ]);
    }

    protected function createCompletedTransaction(): Transaction
    {
        return Transaction::create([
            'transaction_code' => 'TRX-'.strtoupper(uniqid()),
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'shipping_cost' => 0,
            'total_amount' => 75000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'use_escrow' => true,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'status' => Transaction::STATUS_COMPLETED,
            'paid_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);
    }

    public function test_buyer_can_review_seller_on_completed_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Penjual sangat ramah, buku mulus seperti baru!',
                'tags' => ['Respon Cepat', 'Barang Sesuai Deskripsi'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'transaction_id' => $transaction->id,
            'reviewer_id' => $this->buyer->id,
            'reviewee_id' => $this->seller->id,
            'type' => Review::TYPE_SELLER_REVIEW,
            'rating' => 5,
            'comment' => 'Penjual sangat ramah, buku mulus seperti baru!',
        ]);

        $this->seller->refresh();
        $this->assertEquals(1, $this->seller->seller_review_count);
        $this->assertEquals('5.00', $this->seller->seller_rating);
    }

    public function test_seller_can_review_buyer_on_completed_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();

        $response = $this->actingAs($this->seller)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Pembeli tepat waktu saat COD dan komunikatif.',
                'tags' => ['Pembayaran Cepat', 'Tepat Waktu'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'transaction_id' => $transaction->id,
            'reviewer_id' => $this->seller->id,
            'reviewee_id' => $this->buyer->id,
            'type' => Review::TYPE_BUYER_REVIEW,
            'rating' => 5,
        ]);

        $this->buyer->refresh();
        $this->assertEquals(1, $this->buyer->buyer_review_count);
        $this->assertEquals('5.00', $this->buyer->buyer_rating);
    }

    public function test_cannot_review_pending_or_processing_transaction(): void
    {
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-PENDING-001',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'shipping_cost' => 0,
            'total_amount' => 75000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'use_escrow' => true,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'status' => Transaction::STATUS_PROCESSING,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Mencoba review sebelum selesai',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ulasan dan rating hanya dapat diberikan setelah transaksi selesai.');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_cannot_review_same_transaction_twice(): void
    {
        $transaction = $this->createCompletedTransaction();

        // First review
        $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Ulasan pertama',
            ]);

        // Duplicate review attempt
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 4,
                'comment' => 'Ulasan kedua yang tidak sah',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda sudah memberikan ulasan dan rating untuk transaksi ini.');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_unrelated_third_party_cannot_review_transaction(): void
    {
        $transaction = $this->createCompletedTransaction();

        $response = $this->actingAs($this->otherUser)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Saya pihak ketiga yang tidak berhak',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_validation_rules(): void
    {
        $transaction = $this->createCompletedTransaction();

        // Invalid rating: 6 (maximum is 5)
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 6,
            ]);
        $response->assertSessionHasErrors(['rating']);

        // Invalid rating: 0 (minimum is 1)
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 0,
            ]);
        $response->assertSessionHasErrors(['rating']);

        // Invalid comment: > 1000 characters
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => str_repeat('a', 1001),
            ]);
        $response->assertSessionHasErrors(['comment']);
    }

    public function test_rating_recalculation_handles_multiple_reviews(): void
    {
        // First transaction (rating: 5)
        $tx1 = $this->createCompletedTransaction();
        $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $tx1), [
                'rating' => 5,
            ]);

        // Second transaction with another buyer (rating: 3)
        $tx2 = Transaction::create([
            'transaction_code' => 'TRX-SECOND-002',
            'product_id' => $this->product->id,
            'buyer_id' => $this->otherUser->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000,
            'shipping_cost' => 0,
            'total_amount' => 50000,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'status' => Transaction::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->otherUser)
            ->post(route('transactions.reviews.store', $tx2), [
                'rating' => 3,
            ]);

        $this->seller->refresh();
        $this->assertEquals(2, $this->seller->seller_review_count);
        $this->assertEquals('4.00', $this->seller->seller_rating);
        $this->assertStringContainsString('4.0 / 5.0 (2 ulasan)', $this->seller->seller_rating_display);
    }

    public function test_public_reputation_page_renders_reviews_and_statistics(): void
    {
        $transaction = $this->createCompletedTransaction();
        $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Penjual mantap bintang lima!',
                'tags' => ['Respon Cepat'],
            ]);

        $response = $this->actingAs($this->buyer)
            ->get(route('users.reviews', $this->seller));

        $response->assertStatus(200);
        $response->assertSee('Reputasi & Ulasan', false);
        $response->assertSee('Budi Seller');
        $response->assertSee('Penjual mantap bintang lima!');
        $response->assertSee('Respon Cepat');
        $response->assertSee('5.0 / 5.0');
    }

    public function test_completed_transaction_page_displays_review_form_and_submitted_reviews(): void
    {
        $transaction = $this->createCompletedTransaction();

        // 1. Initially shows review form for buyer
        $response = $this->actingAs($this->buyer)
            ->get(route('transactions.show', $transaction));

        $response->assertStatus(200);
        $response->assertSee('Rating & Ulasan', false);
        $response->assertSee('Beri Ulasan untuk Penjual');
        $response->assertSee('Respon Cepat');

        // 2. Submit buyer review
        $this->actingAs($this->buyer)
            ->post(route('transactions.reviews.store', $transaction), [
                'rating' => 5,
                'comment' => 'Buku sangat rapi dan penjual ramah!',
                'tags' => ['Respon Cepat'],
            ]);

        // 3. Now shows submitted review
        $responseAfter = $this->actingAs($this->buyer)
            ->get(route('transactions.show', $transaction));

        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('Ulasan Anda');
        $responseAfter->assertSee('Buku sangat rapi dan penjual ramah!');
    }
}
