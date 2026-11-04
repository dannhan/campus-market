<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOptionsTest extends TestCase
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
            'preferred_meeting_location' => 'Perpustakaan Pusat Undip Lt. 1',
            'preferred_pickup_location' => 'Loker Bersama Gedung E Lantai 2',
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
            'title' => 'Buku Kalkulus Purcel Edisi 9',
            'description' => 'Kondisi mulus tanpa coretan, cocok untuk TPB.',
            'price' => 150000,
            'condition' => 'like_new',
            'status' => 'active',
            'pickup_location' => 'Loker Gedung E Lantai 2',
            'courier_fee' => 12000,
        ]);
    }

    public function test_checkout_screen_displays_delivery_options_and_couriers(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('transactions.checkout', $this->product));

        $response->assertOk();
        $response->assertSee('Opsi Pengiriman & Penyerahan Barang', false);
        $response->assertSee('COD / Ketemu Langsung di Kampus');
        $response->assertSee('Pickup di Kampus (Ambil Sendiri)');
        $response->assertSee('Pengiriman Kurir Ekspedisi');
        $response->assertSee('J&amp;T Express', false);
        $response->assertSee('JNE Express');
        $response->assertSee('SiCepat Ekspres');
        $response->assertSee('Kurir Mahasiswa Kampus');
    }

    public function test_buyer_can_order_with_cod_delivery_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_COD,
                'payment_channel' => 'cod',
                'delivery_method' => Transaction::DELIVERY_METHOD_COD,
                'meeting_location' => 'Gazebo Perpustakaan Pusat Undip',
                'buyer_notes' => 'Ketemu jam 10 pagi ya kak.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'delivery_method' => 'cod',
            'shipping_cost' => 0,
            'amount' => 150000,
            'total_amount' => 150000,
            'meeting_location' => 'Gazebo Perpustakaan Pusat Undip',
        ]);

        $transaction = Transaction::where('product_id', $this->product->id)->first();
        $response->assertRedirect(route('transactions.show', $transaction));
    }

    public function test_buyer_can_order_with_campus_pickup_delivery_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bca',
                'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
                'pickup_location' => 'Loker Bersama Gedung E Lantai 2',
                'buyer_notes' => 'Nanti saya ambil jam 2 siang.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'delivery_method' => 'pickup',
            'shipping_cost' => 0,
            'amount' => 150000,
            'total_amount' => 150000,
            'pickup_location' => 'Loker Bersama Gedung E Lantai 2',
        ]);
    }

    public function test_buyer_can_order_with_courier_delivery_method(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bca',
                'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
                'courier_name' => 'jnt',
                'shipping_recipient_name' => 'Dewi Lestari',
                'shipping_phone' => '081298765432',
                'shipping_address' => 'Kos Putri Melati No. 5, Jl. Prof. Soedarto, Tembalang, Semarang',
                'buyer_notes' => 'Tolong bungkus bubble wrap tebal ya.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'delivery_method' => 'courier',
            'courier_name' => 'jnt',
            'shipping_cost' => 12000,
            'amount' => 150000,
            'total_amount' => 162000,
            'shipping_recipient_name' => 'Dewi Lestari',
            'shipping_phone' => '081298765432',
            'shipping_address' => 'Kos Putri Melati No. 5, Jl. Prof. Soedarto, Tembalang, Semarang',
        ]);

        $transaction = Transaction::where('product_id', $this->product->id)->first();
        $response->assertRedirect(route('transactions.show', $transaction));
    }

    public function test_courier_delivery_requires_recipient_details_and_courier_selection(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.store'), [
                'product_id' => $this->product->id,
                'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_channel' => 'bca',
                'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
                'courier_name' => '',
                'shipping_recipient_name' => '',
                'shipping_phone' => '',
                'shipping_address' => '',
            ]);

        $response->assertSessionHasErrors([
            'courier_name',
            'shipping_recipient_name',
            'shipping_phone',
            'shipping_address',
        ]);
    }

    public function test_seller_can_input_tracking_number_and_ship_item(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 150000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'courier_name' => 'jnt',
            'shipping_cost' => 12000,
            'total_amount' => 162000,
            'shipping_recipient_name' => 'Dewi Lestari',
            'shipping_phone' => '081298765432',
            'shipping_address' => 'Kos Melati Tembalang',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $response = $this->actingAs($this->seller)
            ->post(route('transactions.ship', $transaction), [
                'courier_name' => 'jnt',
                'tracking_number' => 'JX876543210ID',
                'seller_notes' => 'Paket sudah diserahkan ke gerai J&T Tembalang.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $transaction->refresh();
        $this->assertSame('JX876543210ID', $transaction->tracking_number);
        $this->assertNotNull($transaction->shipped_at);
        $this->assertSame('Paket sudah diserahkan ke gerai J&T Tembalang.', $transaction->seller_notes);
    }

    public function test_unauthorized_user_cannot_input_tracking_number(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 150000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'courier_name' => 'jnt',
            'shipping_cost' => 12000,
            'total_amount' => 162000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        // Buyer cannot ship
        $this->actingAs($this->buyer)
            ->post(route('transactions.ship', $transaction), [
                'tracking_number' => 'HACK123',
            ])
            ->assertForbidden();

        // Other student cannot ship
        $this->actingAs($this->otherStudent)
            ->post(route('transactions.ship', $transaction), [
                'tracking_number' => 'HACK123',
            ])
            ->assertForbidden();
    }

    public function test_buyer_can_mark_item_as_delivered(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 150000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'courier_name' => 'jnt',
            'shipping_cost' => 12000,
            'total_amount' => 162000,
            'tracking_number' => 'JX876543210ID',
            'shipped_at' => now()->subDay(),
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('transactions.delivered', $transaction));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $transaction->refresh();
        $this->assertNotNull($transaction->delivered_at);
    }

    public function test_transactions_can_be_filtered_by_delivery_method(): void
    {
        $codTrx = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 150000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'meeting_location' => 'Kantin FT Undip',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $secondProduct = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Flashdisk Sandisk 64GB',
            'description' => 'Original USB 3.0',
            'price' => 75000,
            'condition' => 'like_new',
            'status' => 'sold',
        ]);

        $courierTrx = Transaction::create([
            'product_id' => $secondProduct->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'courier_name' => 'jnt',
            'shipping_cost' => 10000,
            'total_amount' => 85000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        // Filter by courier
        $responseCourier = $this->actingAs($this->buyer)
            ->get(route('transactions.index', ['delivery' => 'courier']));

        $responseCourier->assertOk();
        $responseCourier->assertSee($secondProduct->title);
        $responseCourier->assertDontSee($this->product->title);

        // Filter by cod
        $responseCod = $this->actingAs($this->buyer)
            ->get(route('transactions.index', ['delivery' => 'cod']));

        $responseCod->assertOk();
        $responseCod->assertSee($this->product->title);
        $responseCod->assertDontSee($secondProduct->title);
    }

    public function test_user_can_update_preferred_pickup_location_in_payment_settings(): void
    {
        $response = $this->actingAs($this->seller)
            ->put(route('profile.payments.update'), [
                'bank_name' => 'bca',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Budi Santoso',
                'ewallet_provider' => 'gopay',
                'ewallet_account_number' => '081234567890',
                'ewallet_account_name' => 'Budi Santoso',
                'preferred_meeting_location' => 'Perpustakaan Pusat Undip',
                'preferred_pickup_location' => 'Sekretariat HMIF Gedung D Lantai 1',
            ]);

        $response->assertRedirect(route('profile.payments.edit'));
        $response->assertSessionHas('status');

        $this->seller->refresh();
        $this->assertSame('Sekretariat HMIF Gedung D Lantai 1', $this->seller->preferred_pickup_location);
    }
}
