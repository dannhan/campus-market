<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\EscrowPayment;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EscrowPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected User $otherStudent;

    protected Product $product;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

        $this->admin = User::factory()->create([
            'name' => 'Admin CampusMarket',
            'email' => 'admin@campusmarket.test',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->seller = User::factory()->forStudent($sellerStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
            'bank_name' => 'bca',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
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
            'title' => 'Buku Algoritma & Struktur Data',
            'description' => 'Buku wajib untuk mahasiswa informatika semester 3.',
            'price' => 125000,
            'condition' => 'used',
            'status' => 'active',
            'courier_fee' => 10000,
        ]);
    }

    public function test_checkout_screen_displays_escrow_protection_guarantee(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('transactions.checkout', $this->product));

        $response->assertOk();
        $response->assertSee('Garansi Rekber Kampus (Rekening Bersama)');
        $response->assertSee('Dana transfer Anda ditahan aman di rekening penampung resmi CampusMarket');
    }

    public function test_digital_payment_checkout_creates_escrow_payment_record(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bca',
                'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
                'use_escrow' => '1',
                'buyer_notes' => 'Siap transfer via Rekber.',
            ]);

        $transaction = Transaction::where('product_id', $this->product->id)->firstOrFail();
        $this->assertTrue($transaction->use_escrow);
        $this->assertNotNull($transaction->escrowPayment);

        $escrow = $transaction->escrowPayment;
        $this->assertSame(EscrowPayment::STATUS_PENDING_DEPOSIT, $escrow->status);
        $this->assertSame(125000.0, (float) $escrow->amount);
        $this->assertSame(125000.0, (float) $escrow->total_amount);
        $this->assertSame('bca', $escrow->holding_channel);
        $this->assertSame('8801-2345-67', $escrow->holding_account_number);

        $response->assertRedirect(route('transactions.show', $transaction));
    }

    public function test_cod_order_does_not_create_escrow_payment(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_COD,
                'payment_channel' => 'cod',
                'delivery_method' => Transaction::DELIVERY_METHOD_COD,
                'meeting_location' => 'Kantin Gedung Kuliah Bersama',
            ]);

        $transaction = Transaction::where('product_id', $this->product->id)->firstOrFail();
        $this->assertFalse($transaction->use_escrow);
        $this->assertNull($transaction->escrowPayment);
    }

    public function test_buyer_uploading_payment_proof_updates_escrow_status_to_held(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'shipping_cost' => 0,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_PENDING_DEPOSIT,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
        ]);

        $proofFile = UploadedFile::fake()->create('struk_rekber.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.proof', $transaction), [
                'payment_proof' => $proofFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_HELD, $escrow->status);
        $this->assertNotNull($escrow->deposit_proof);
        $this->assertNotNull($escrow->deposited_at);

        $transaction->refresh();
        $this->assertSame(Transaction::PAYMENT_STATUS_PAID, $transaction->payment_status);
        $this->assertSame(Transaction::STATUS_PROCESSING, $transaction->status);
    }

    public function test_buyer_can_release_held_escrow_funds_to_seller(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'shipping_cost' => 0,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
            'deposited_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.release-escrow', $transaction));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_RELEASED, $escrow->status);
        $this->assertNotNull($escrow->released_at);

        $transaction->refresh();
        $this->assertSame(Transaction::STATUS_COMPLETED, $transaction->status);
        $this->assertSame(Transaction::PAYMENT_STATUS_CONFIRMED, $transaction->payment_status);
        $this->assertNotNull($transaction->confirmed_at);
    }

    public function test_unauthorized_user_cannot_release_escrow_funds(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'use_escrow' => true,
        ]);

        EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
        ]);

        // Seller cannot release buyer's escrow funds themselves
        $this->actingAs($this->seller)
            ->post(route('transactions.release-escrow', $transaction))
            ->assertForbidden();

        // Other student cannot release
        $this->actingAs($this->otherStudent)
            ->post(route('transactions.release-escrow', $transaction))
            ->assertForbidden();
    }

    public function test_admin_can_access_escrow_panel_and_see_holdings(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
        ]);

        // Non-admin gets forbidden
        $this->actingAs($this->buyer)
            ->get(route('admin.escrows.index'))
            ->assertForbidden();

        // Admin gets access
        $response = $this->actingAs($this->admin)
            ->get(route('admin.escrows.index'));

        $response->assertOk();
        $response->assertSee('Rekening Bersama (Escrow)');
        $response->assertSee($escrow->escrow_code);
        $response->assertSee('Rp 125.000');
    }

    public function test_admin_can_verify_and_release_escrow(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_PENDING_DEPOSIT,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
            'deposit_proof' => 'payment_proofs/struk.jpg',
        ]);

        // 1. Admin verifies deposit
        $verifyResponse = $this->actingAs($this->admin)
            ->post(route('admin.escrows.verify', $escrow));

        $verifyResponse->assertRedirect();
        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_HELD, $escrow->status);
        $this->assertSame($this->admin->id, $escrow->verified_by_id);

        // 2. Admin releases funds
        $releaseResponse = $this->actingAs($this->admin)
            ->post(route('admin.escrows.release', $escrow), [
                'notes' => 'Barang telah diterima pembeli, dana dicairkan.',
            ]);

        $releaseResponse->assertRedirect();
        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_RELEASED, $escrow->status);
        $this->assertNotNull($escrow->released_at);

        $transaction->refresh();
        $this->assertSame(Transaction::STATUS_COMPLETED, $transaction->status);
    }

    public function test_admin_can_refund_escrow_to_buyer(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.escrows.refund', $escrow), [
                'reason' => 'Penjual berhalangan hadir dan membatalkan pesanan.',
            ]);

        $response->assertRedirect();
        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_REFUNDED, $escrow->status);
        $this->assertNotNull($escrow->refunded_at);

        $transaction->refresh();
        $this->assertSame(Transaction::STATUS_CANCELLED, $transaction->status);

        $this->product->refresh();
        $this->assertSame('active', $this->product->status);
    }

    public function test_filing_dispute_on_held_escrow_freezes_funds_and_admin_can_resolve(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'total_amount' => 125000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'use_escrow' => true,
        ]);

        $escrow = EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 125000,
            'shipping_cost' => 0,
            'platform_fee' => 0,
            'total_amount' => 125000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
        ]);

        // 1. Buyer files dispute
        $disputeResponse = $this->actingAs($this->buyer)
            ->post(route('disputes.store'), [
                'product_id' => $this->product->id,
                'title' => 'Buku yang dikirim rusak parah',
                'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
                'description' => 'Halaman buku banyak yang hilang dan robek.',
                'solution_requested' => Dispute::SOLUTION_REFUND,
            ]);

        $dispute = Dispute::where('product_id', $this->product->id)->firstOrFail();
        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_DISPUTED, $escrow->status);

        // 2. Admin resolves dispute in favor of buyer -> refunds escrow
        $resolveResponse = $this->actingAs($this->admin)
            ->put(route('admin.disputes.update', $dispute), [
                'status' => Dispute::STATUS_RESOLVED,
                'admin_decision' => Dispute::DECISION_FAVOR_BUYER,
                'admin_notes' => 'Klaim pembeli terbukti benar. Dana rekber dikembalikan penuh kepada pembeli.',
            ]);

        $resolveResponse->assertRedirect();

        $escrow->refresh();
        $this->assertSame(EscrowPayment::STATUS_REFUNDED, $escrow->status);
        $this->assertNotNull($escrow->refunded_at);

        $transaction->refresh();
        $this->assertSame(Transaction::STATUS_CANCELLED, $transaction->status);
    }
}
