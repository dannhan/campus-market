<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\EscrowPayment;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use App\Services\Transaction\TransactionTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected Product $product;

    protected Category $category;

    protected University $university;

    protected TransactionTimelineService $timelineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timelineService = new TransactionTimelineService;

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
        ]);

        $sellerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@students.undip.ac.id',
            'student_id' => $sellerStudent->id,
        ]);

        $buyerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130002',
            'full_name' => 'Siti Rahmawati',
            'major' => 'Sistem Informasi',
            'university_email' => 'siti.rahmawati@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@students.undip.ac.id',
            'student_id' => $buyerStudent->id,
        ]);

        $this->category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Algoritma & Pemrograman',
            'slug' => 'buku-algoritma-pemrograman',
            'description' => 'Buku teks kuliah kondisi mulus dan lengkap.',
            'price' => 50000.00,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Tembalang, Semarang',
            'bargainable' => false,
            'delivery_methods' => ['cod', 'pickup', 'courier'],
            'pickup_location' => 'Loker Himpunan Informatika',
            'courier_fee' => 9000.00,
        ]);
    }

    public function test_courier_transaction_timeline_initial_pending_state(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
            'use_escrow' => true,
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        $this->assertEquals(5, $timeline['total_steps']);
        $this->assertEquals(2, $timeline['current_step']);
        $this->assertFalse($timeline['is_completed']);
        $this->assertFalse($timeline['is_cancelled']);
        $this->assertGreaterThan(0, $timeline['progress_percentage']);

        // Check step statuses
        $this->assertEquals('completed', $timeline['steps'][0]['state']);
        $this->assertEquals('current', $timeline['steps'][1]['state']);
        $this->assertEquals('upcoming', $timeline['steps'][2]['state']);
        $this->assertEquals('upcoming', $timeline['steps'][3]['state']);
        $this->assertEquals('upcoming', $timeline['steps'][4]['state']);

        // Check events audit log
        $this->assertNotEmpty($timeline['events']);
        $this->assertEquals('Pesanan Berhasil Dibuat', $timeline['events'][0]['title']);
    }

    public function test_timeline_reflects_escrow_held_and_shipped_state(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'tracking_number' => 'JNT88992211',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_PROCESSING,
            'paid_at' => now()->subHours(5),
            'shipped_at' => now()->subHours(2),
            'use_escrow' => true,
        ]);

        EscrowPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => 50000.00,
            'shipping_cost' => 9000.00,
            'platform_fee' => 0.00,
            'total_amount' => 59000.00,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '1234567890',
            'holding_account_name' => 'CampusMarket Rekber',
            'verified_at' => now()->subHours(4),
            'seller_payout_details' => [],
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        // Step 1: Created (completed)
        $this->assertEquals('completed', $timeline['steps'][0]['state']);
        // Step 2: Escrow Protected (completed)
        $this->assertEquals('completed', $timeline['steps'][1]['state']);
        // Step 3: Courier Shipped (completed, tracking number included)
        $this->assertEquals('completed', $timeline['steps'][2]['state']);
        $this->assertStringContainsString('JNT88992211', $timeline['steps'][2]['description']);
        // Step 4: Package Arriving / In Transit (current active)
        $this->assertEquals('current', $timeline['steps'][3]['state']);
        $this->assertEquals(4, $timeline['current_step']);
        $this->assertGreaterThanOrEqual(60, $timeline['progress_percentage']);
    }

    public function test_timeline_progress_one_hundred_percent_when_completed(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'tracking_number' => 'JNT88992211',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_COMPLETED,
            'paid_at' => now()->subDays(2),
            'shipped_at' => now()->subDay(),
            'delivered_at' => now()->subHours(5),
            'confirmed_at' => now()->subHours(1),
            'use_escrow' => true,
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        $this->assertTrue($timeline['is_completed']);
        $this->assertEquals(100, $timeline['progress_percentage']);
        $this->assertEquals(5, $timeline['current_step']);

        foreach ($timeline['steps'] as $step) {
            $this->assertEquals('completed', $step['state']);
        }
    }

    public function test_timeline_handles_cancellation(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CANCELLED,
            'status' => Transaction::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        $this->assertTrue($timeline['is_cancelled']);
        $this->assertEquals('Transaksi Dibatalkan', $timeline['status_title']);

        $cancellationEvents = array_filter($timeline['events'], fn ($e) => str_contains($e['title'], 'Dibatalkan'));
        $this->assertNotEmpty($cancellationEvents);
    }

    public function test_cod_transaction_timeline_has_four_steps(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'shipping_cost' => 0.00,
            'total_amount' => 50000.00,
            'meeting_location' => 'Depan Perpustakaan Pusat Undip',
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        $this->assertEquals(4, $timeline['total_steps']);
        $this->assertEquals('Pesanan COD Dibuat', $timeline['steps'][0]['title']);
        $this->assertEquals('Titik Janji Temu', $timeline['steps'][1]['title']);
        $this->assertEquals('Pertemuan & Cek Fisik', $timeline['steps'][2]['title']);
        $this->assertEquals('Bayar Tunai & Selesai', $timeline['steps'][3]['title']);
        $this->assertStringContainsString('Perpustakaan Pusat Undip', $timeline['steps'][1]['description']);
    }

    public function test_campus_pickup_timeline_has_five_steps(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'shipping_cost' => 0.00,
            'total_amount' => 50000.00,
            'pickup_location' => 'Loker Himpunan Informatika Gedung E',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_PROCESSING,
            'paid_at' => now(),
            'use_escrow' => true,
        ]);

        $timeline = $this->timelineService->getTimeline($transaction);

        $this->assertEquals(5, $timeline['total_steps']);
        $this->assertEquals('Siap di Titik Kampus', $timeline['steps'][2]['title']);
        $this->assertEquals('Barang Diambil', $timeline['steps'][3]['title']);
        $this->assertStringContainsString('Loker Himpunan Informatika', $timeline['steps'][2]['description']);
    }

    public function test_transaction_show_page_renders_visual_timeline(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'tracking_number' => 'JNT778899',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_PROCESSING,
            'paid_at' => now()->subDay(),
            'shipped_at' => now()->subHours(3),
            'use_escrow' => true,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('transactions.show', $transaction));

        $response->assertOk()
            ->assertSee('Status Pelacakan Transaksi')
            ->assertSee('Progres Transaksi')
            ->assertSee('Riwayat Jejak Waktu Aktivitas Transaksi')
            ->assertSee('Kirim Ekspedisi')
            ->assertSee('Paket Tiba');
    }

    public function test_transaction_index_page_displays_mini_progress_indicator(): void
    {
        Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'shipping_cost' => 9000.00,
            'total_amount' => 59000.00,
            'courier_name' => 'jnt',
            'shipping_recipient_name' => 'Siti Rahmawati',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
            'use_escrow' => true,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('transactions.index'));

        $response->assertOk()
            ->assertSee('Riwayat Transaksi')
            ->assertSee('Menunggu Pembayaran');
    }
}
