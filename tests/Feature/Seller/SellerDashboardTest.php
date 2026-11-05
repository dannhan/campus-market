<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\EscrowPayment;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected User $otherSeller;

    protected Product $product;

    protected Category $category;

    protected University $university;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Indonesia',
            'slug' => 'ui',
            'email_domain' => 'ui.ac.id',
            'city' => 'Kota Depok',
            'province' => 'Jawa Barat',
        ]);

        $sellerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906001001',
            'full_name' => 'Budi Seller',
            'major' => 'Ilmu Komputer',
            'university_email' => 'budi.seller@ui.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Budi Seller',
            'email' => 'budi.seller@ui.ac.id',
            'student_id' => $sellerStudent->id,
            'student_status' => 'active',
            'bank_name' => 'BCA',
            'bank_account_name' => 'Budi Seller',
            'bank_account_number' => '1234567890',
        ]);

        $buyerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906002002',
            'full_name' => 'Siti Buyer',
            'major' => 'Manajemen',
            'university_email' => 'siti.buyer@ui.ac.id',
            'is_active' => true,
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Siti Buyer',
            'email' => 'siti.buyer@ui.ac.id',
            'student_id' => $buyerStudent->id,
            'student_status' => 'active',
        ]);

        $otherStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906003003',
            'full_name' => 'Doni Other',
            'major' => 'Hukum',
            'university_email' => 'doni.other@ui.ac.id',
            'is_active' => true,
        ]);

        $this->otherSeller = User::factory()->create([
            'name' => 'Doni Other',
            'email' => 'doni.other@ui.ac.id',
            'student_id' => $otherStudent->id,
            'student_status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'icon' => 'book',
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Kalkulus Thomas Edisi 13',
            'slug' => 'buku-kalkulus-thomas-edisi-13',
            'description' => 'Buku teks kalkulus lengkap kondisi sangat mulus tanpa coretan.',
            'price' => 125000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'delivery_methods' => ['pickup_point', 'courier'],
            'status' => 'active',
            'views_count' => 42,
        ]);
    }

    public function test_guests_cannot_access_seller_center_pages(): void
    {
        $this->get(route('seller.dashboard'))->assertRedirect(route('login'));
        $this->get(route('seller.orders'))->assertRedirect(route('login'));
        $this->get(route('seller.products'))->assertRedirect(route('login'));
    }

    public function test_seller_dashboard_displays_correct_revenue_and_escrow_balances(): void
    {
        // 1. Create a completed transaction (contributes to total revenue)
        $completedTrx = Transaction::create([
            'transaction_code' => 'TRX-TEST-0001',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'courier_fee' => 10000,
            'total_amount' => 135000,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
            'delivery_method' => 'pickup_point',
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        // 2. Create a processing transaction with held escrow (contributes to held balance and action required)
        $processingTrx = Transaction::create([
            'transaction_code' => 'TRX-TEST-0002',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'courier_fee' => 10000,
            'total_amount' => 135000,
            'payment_method' => 'escrow',
            'payment_status' => 'paid',
            'delivery_method' => 'courier',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        EscrowPayment::create([
            'transaction_id' => $processingTrx->id,
            'payment_method' => 'bank_transfer',
            'amount' => 125000,
            'shipping_cost' => 10000,
            'platform_fee' => 0,
            'total_amount' => 135000,
            'status' => EscrowPayment::STATUS_HELD,
            'holding_channel' => 'bca',
            'holding_account_number' => '8801-2345-67',
            'holding_account_name' => 'Rekber CampusMarket Indonesia',
            'held_at' => now(),
        ]);

        $response = $this->actingAs($this->seller)->get(route('seller.dashboard'));

        $response->assertOk();
        $response->assertSee('Seller Center');
        $response->assertSee('Rp 125.000'); // Completed revenue
        $response->assertSee('Rp 135.000'); // Held escrow balance
        $response->assertSee('Pesanan Perlu Diproses!');
        $response->assertSee('TRX-TEST-0002');
        $response->assertSee('Buku Kalkulus Thomas Edisi 13');
        $response->assertSee('Rekening Bank dan E-Wallet Siap Menerima Pembayaran');
    }

    public function test_seller_orders_page_renders_orders_and_filters_correctly(): void
    {
        // Completed order
        Transaction::create([
            'transaction_code' => 'TRX-CMPL-0001',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'courier_fee' => 0,
            'total_amount' => 125000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'delivery_method' => 'cod',
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        // Action required order (processing)
        Transaction::create([
            'transaction_code' => 'TRX-PROC-0002',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'courier_fee' => 10000,
            'total_amount' => 135000,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
            'delivery_method' => 'courier',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        // Cancelled order
        Transaction::create([
            'transaction_code' => 'TRX-CNCL-0003',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 125000,
            'courier_fee' => 0,
            'total_amount' => 125000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'delivery_method' => 'cod',
            'status' => Transaction::STATUS_CANCELLED,
        ]);

        // Test All filter
        $responseAll = $this->actingAs($this->seller)->get(route('seller.orders', ['status' => 'all']));
        $responseAll->assertOk();
        $responseAll->assertSee('TRX-CMPL-0001');
        $responseAll->assertSee('TRX-PROC-0002');
        $responseAll->assertSee('TRX-CNCL-0003');

        // Test Action Required filter
        $responseAction = $this->actingAs($this->seller)->get(route('seller.orders', ['status' => 'action_required']));
        $responseAction->assertOk();
        $responseAction->assertSee('TRX-PROC-0002');
        $responseAction->assertDontSee('TRX-CMPL-0001');
        $responseAction->assertDontSee('TRX-CNCL-0003');

        // Test Completed filter
        $responseCompleted = $this->actingAs($this->seller)->get(route('seller.orders', ['status' => 'completed']));
        $responseCompleted->assertOk();
        $responseCompleted->assertSee('TRX-CMPL-0001');
        $responseCompleted->assertDontSee('TRX-PROC-0002');

        // Test Cancelled filter
        $responseCancelled = $this->actingAs($this->seller)->get(route('seller.orders', ['status' => 'cancelled']));
        $responseCancelled->assertOk();
        $responseCancelled->assertSee('TRX-CNCL-0003');
        $responseCancelled->assertDontSee('TRX-PROC-0002');
    }

    public function test_seller_products_page_renders_inventory_and_filters(): void
    {
        // 2nd product: withdrawn
        $withdrawnProduct = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Jas Lab Kimia Ukuran L',
            'slug' => 'jas-lab-kimia-ukuran-l',
            'description' => 'Jas lab bersih sudah di-laundry.',
            'price' => 50000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'delivery_methods' => ['pickup_point'],
            'status' => 'withdrawn',
            'views_count' => 15,
        ]);

        // Test All filter
        $response = $this->actingAs($this->seller)->get(route('seller.products'));
        $response->assertOk();
        $response->assertSee('Buku Kalkulus Thomas Edisi 13');
        $response->assertSee('Jas Lab Kimia Ukuran L');
        $response->assertSee('Rp 125.000');
        $response->assertSee('Rp 50.000');

        // Test Active filter
        $responseActive = $this->actingAs($this->seller)->get(route('seller.products', ['status' => 'active']));
        $responseActive->assertOk();
        $responseActive->assertSee('Buku Kalkulus Thomas Edisi 13');
        $responseActive->assertDontSee('Jas Lab Kimia Ukuran L');

        // Test Withdrawn filter
        $responseWithdrawn = $this->actingAs($this->seller)->get(route('seller.products', ['status' => 'withdrawn']));
        $responseWithdrawn->assertOk();
        $responseWithdrawn->assertSee('Jas Lab Kimia Ukuran L');
        $responseWithdrawn->assertDontSee('Buku Kalkulus Thomas Edisi 13');
    }

    public function test_seller_can_toggle_product_status_between_active_and_withdrawn(): void
    {
        $this->assertEquals('active', $this->product->fresh()->status);

        // Toggle active -> withdrawn
        $response = $this->actingAs($this->seller)->post(route('seller.products.status', $this->product));
        $response->assertRedirect();
        $this->assertEquals('withdrawn', $this->product->fresh()->status);

        // Toggle withdrawn -> active
        $response2 = $this->actingAs($this->seller)->post(route('seller.products.status', $this->product));
        $response2->assertRedirect();
        $this->assertEquals('active', $this->product->fresh()->status);
    }

    public function test_user_cannot_toggle_status_of_another_sellers_product(): void
    {
        $response = $this->actingAs($this->otherSeller)->post(route('seller.products.status', $this->product));
        $response->assertForbidden();

        // Status should remain unchanged
        $this->assertEquals('active', $this->product->fresh()->status);
    }

    public function test_profile_page_displays_seller_center_card_with_metrics(): void
    {
        $response = $this->actingAs($this->seller)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('Seller Center');
        $response->assertSee('Total Pendapatan');
        $response->assertSee('Barang Aktif');
        $response->assertSee('1 Produk');
    }
}
