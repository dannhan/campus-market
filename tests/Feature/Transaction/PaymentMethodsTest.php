<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected User $otherStudent;

    protected Product $product;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $undip = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $ugm = University::create([
            'name' => 'Universitas Gadjah Mada',
            'slug' => 'ugm',
            'email_domain' => 'mail.ugm.ac.id',
        ]);

        $sellerStudent = Student::create([
            'university_id' => $undip->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $buyerStudent = Student::create([
            'university_id' => $ugm->id,
            'student_number' => '22/498765/TK/54321',
            'full_name' => 'Dewi Lestari',
            'major' => 'Teknik Industri',
            'university_email' => 'dewi.lestari@mail.ugm.ac.id',
            'is_active' => true,
        ]);

        $otherStudent = Student::create([
            'university_id' => $undip->id,
            'student_number' => '24060122130099',
            'full_name' => 'Rian Hidayat',
            'major' => 'Fisika',
            'university_email' => 'rian.hidayat@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->forStudent($sellerStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
            'bank_name' => 'bca',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
            'ewallet_provider' => 'gopay',
            'ewallet_account_number' => '081234567890',
            'ewallet_account_name' => 'Budi Santoso',
            'preferred_meeting_location' => 'Perpustakaan Pusat Undip Lt. 1',
        ]);

        $this->buyer = User::factory()->forStudent($buyerStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->otherStudent = User::factory()->forStudent($otherStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'description' => 'Buku referensi kuliah',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Fisika Dasar Halliday Resnick',
            'slug' => 'buku-fisika-dasar-halliday-resnick',
            'description' => 'Edisi 10 jilid 1, sangat rapi.',
            'price' => 120000,
            'condition' => 'like_new',
            'status' => 'active',
            'bargainable' => true,
        ]);
    }

    public function test_guest_cannot_access_payment_and_transaction_routes(): void
    {
        $this->get(route('transactions.index'))->assertRedirect(route('login'));
        $this->get(route('transactions.checkout', $this->product))->assertRedirect(route('login'));
        $this->get(route('profile.payments.edit'))->assertRedirect(route('login'));
    }

    public function test_student_can_update_payment_receiving_accounts(): void
    {
        $response = $this->actingAs($this->seller)
            ->put(route('profile.payments.update'), [
                'bank_name' => 'mandiri',
                'bank_account_number' => '9876543210',
                'bank_account_name' => 'Budi Santoso Mandiri',
                'ewallet_provider' => 'dana',
                'ewallet_account_number' => '089876543210',
                'ewallet_account_name' => 'Budi DANA',
                'preferred_meeting_location' => 'Kantin Gedung Dekanat FT',
            ]);

        $response->assertRedirect(route('profile.payments.edit'));
        $response->assertSessionHas('status');

        $this->seller->refresh();
        $this->assertSame('mandiri', $this->seller->bank_name);
        $this->assertSame('9876543210', $this->seller->bank_account_number);
        $this->assertSame('dana', $this->seller->ewallet_provider);
        $this->assertSame('Kantin Gedung Dekanat FT', $this->seller->preferred_meeting_location);
    }

    public function test_buyer_can_view_checkout_page_for_active_product(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('transactions.checkout', $this->product));

        $response->assertOk();
        $response->assertSeeText('Checkout Pembayaran');
        $response->assertSeeText('Buku Fisika Dasar Halliday Resnick');
        $response->assertSeeText('Bayar Tunai di Kampus (COD)');
        $response->assertSeeText('Transfer Bank Mahasiswa');
        $response->assertSeeText('Dompet Digital (E-Wallet)');
    }

    public function test_buyer_cannot_checkout_own_product(): void
    {
        $this->actingAs($this->seller)
            ->get(route('transactions.checkout', $this->product))
            ->assertForbidden();
    }

    public function test_buyer_can_order_with_cod_payment_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_COD,
                'payment_channel' => 'cod',
                'meeting_location' => 'Depan Gazebo Fakultas Sains',
                'buyer_notes' => 'Bisa ketemu jam 1 siang setelah kuliah selesai.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => 'cod',
            'payment_channel' => 'cod',
            'meeting_location' => 'Depan Gazebo Fakultas Sains',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $this->product->refresh();
        $this->assertSame('sold', $this->product->status);

        $transaction = Transaction::where('product_id', $this->product->id)->first();
        $response->assertRedirect(route('transactions.show', $transaction));
        $response->assertSessionHas('status');
    }

    public function test_buyer_can_order_with_bank_transfer_payment_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bca',
                'buyer_notes' => 'Akan segera ditransfer lewat m-BCA.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'payment_method' => 'bank_transfer',
            'payment_channel' => 'bca',
            'status' => Transaction::STATUS_PENDING,
        ]);

        $transaction = Transaction::where('product_id', $this->product->id)->first();
        $this->assertNotNull($transaction->payment_details);
        $this->assertSame('1234567890', $transaction->payment_details['bank_account_number']);
    }

    public function test_buyer_can_order_with_ewallet_payment_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_EWALLET,
                'payment_channel' => 'gopay',
                'buyer_notes' => 'Transfer via GoPay.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'payment_method' => 'e_wallet',
            'payment_channel' => 'gopay',
        ]);
    }

    public function test_cod_order_requires_meeting_location(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_COD,
                'payment_channel' => 'cod',
                'meeting_location' => '',
            ]);

        $response->assertSessionHasErrors(['meeting_location']);
    }

    public function test_buyer_can_checkout_with_accepted_offer_price(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 100000,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'offer_id' => $offer->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bni',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'offer_id' => $offer->id,
            'amount' => 100000,
            'payment_channel' => 'bni',
        ]);
    }

    public function test_buyer_can_upload_payment_proof(): void
    {
        Storage::fake('public');

        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
        ]);

        $file = UploadedFile::fake()->create('struk_transfer.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.proof', $transaction), [
                'payment_proof' => $file,
                'buyer_notes' => 'Sudah ditransfer dari rekening Dewi.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $transaction->refresh();
        $this->assertNotNull($transaction->payment_proof);
        $this->assertSame(Transaction::PAYMENT_STATUS_PAID, $transaction->payment_status);
        $this->assertSame(Transaction::STATUS_PROCESSING, $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        Storage::disk('public')->assertExists($transaction->payment_proof);
    }

    public function test_seller_can_confirm_payment_received(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PROCESSING,
            'meeting_location' => 'Kantin Kampus',
        ]);

        $response = $this->actingAs($this->seller)
            ->post(route('transactions.confirm', $transaction));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $transaction->refresh();
        $this->assertSame(Transaction::PAYMENT_STATUS_CONFIRMED, $transaction->payment_status);
        $this->assertSame(Transaction::STATUS_COMPLETED, $transaction->status);
        $this->assertNotNull($transaction->confirmed_at);
    }

    public function test_user_can_view_transactions_index_and_filter(): void
    {
        Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $response = $this->actingAs($this->buyer)
            ->get(route('transactions.index', ['method' => 'cod']));

        $response->assertOk();
        $response->assertSeeText('Riwayat Transaksi & Pembayaran');
        $response->assertSeeText('Buku Fisika Dasar Halliday Resnick');
    }

    public function test_unrelated_user_cannot_access_or_confirm_others_transaction(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $this->actingAs($this->otherStudent)
            ->get(route('transactions.show', $transaction))
            ->assertForbidden();

        $this->actingAs($this->otherStudent)
            ->post(route('transactions.confirm', $transaction))
            ->assertForbidden();
    }

    public function test_pending_transaction_can_be_cancelled_and_reactivates_product(): void
    {
        $this->product->update(['status' => 'sold']);

        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 120000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.cancel', $transaction));

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertSame(Transaction::STATUS_CANCELLED, $transaction->status);

        $this->product->refresh();
        $this->assertSame('active', $this->product->status);
    }
}
