<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use App\Services\Shipping\CampusShippingRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateEstimatorTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected Product $product;

    protected Category $category;

    protected University $undip;

    protected University $unnes;

    protected University $ugm;

    protected University $ui;

    protected CampusShippingRateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CampusShippingRateService;

        // Universities with real coordinates
        $this->undip = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'latitude' => -7.0504000,
            'longitude' => 110.4393000,
            'address' => 'Jl. Prof. Soedarto, SH, Tembalang, Kota Semarang',
        ]);

        $this->unnes = University::create([
            'name' => 'Universitas Negeri Semarang',
            'slug' => 'unnes',
            'email_domain' => 'mail.unnes.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'latitude' => -7.0494000,
            'longitude' => 110.4076000,
            'address' => 'Sekaran, Gunungpati, Kota Semarang',
        ]);

        $this->ugm = University::create([
            'name' => 'Universitas Gadjah Mada',
            'slug' => 'ugm',
            'email_domain' => 'mail.ugm.ac.id',
            'city' => 'Kab. Sleman',
            'province' => 'D.I. Yogyakarta',
            'latitude' => -7.7713000,
            'longitude' => 110.3775000,
            'address' => 'Bulaksumur, Sleman, Yogyakarta',
        ]);

        $this->ui = University::create([
            'name' => 'Universitas Indonesia',
            'slug' => 'ui',
            'email_domain' => 'ui.ac.id',
            'city' => 'Kota Depok',
            'province' => 'Jawa Barat',
            'latitude' => -6.3653000,
            'longitude' => 106.8286000,
            'address' => 'Pondok Cina, Beji, Depok',
        ]);

        // Seller at Undip
        $sellerStudent = Student::create([
            'university_id' => $this->undip->id,
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
            'bank_name' => 'bca',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
            'ewallet_provider' => 'gopay',
            'ewallet_account_number' => '081234567890',
            'ewallet_account_name' => 'Budi Santoso',
            'preferred_meeting_location' => 'Perpustakaan Undip',
            'preferred_pickup_location' => 'Gedung A Lab Komputer',
        ]);

        // Buyer at UGM
        $buyerStudent = Student::create([
            'university_id' => $this->ugm->id,
            'student_number' => '22/498765/TK/54321',
            'full_name' => 'Dewi Lestari',
            'major' => 'Teknik Industri',
            'university_email' => 'dewi.lestari@mail.ugm.ac.id',
            'is_active' => true,
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@mail.ugm.ac.id',
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
            'title' => 'Buku Kalkulus Purcel Edisi 9',
            'slug' => 'buku-kalkulus-purcel-edisi-9',
            'description' => 'Buku masih bagus dan mulus tanpa coretan.',
            'price' => 75000.00,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Tembalang, Semarang',
            'bargainable' => true,
            'delivery_methods' => ['cod', 'pickup', 'courier'],
            'pickup_location' => 'Loker Bersama Lab Informatika Undip',
            'courier_fee' => 10000.00,
        ]);
    }

    public function test_haversine_distance_calculation_is_accurate(): void
    {
        // Distance between Undip Semarang and Unnes Semarang should be ~3.5 to 6 km
        $distanceSameCity = $this->service->calculateDistanceKm(
            (float) $this->undip->latitude,
            (float) $this->undip->longitude,
            (float) $this->unnes->latitude,
            (float) $this->unnes->longitude
        );

        $this->assertGreaterThan(2.0, $distanceSameCity);
        $this->assertLessThan(10.0, $distanceSameCity);

        // Distance between Undip Semarang and UGM Sleman should be ~85 to 105 km
        $distanceRegional = $this->service->calculateDistanceKm(
            (float) $this->undip->latitude,
            (float) $this->undip->longitude,
            (float) $this->ugm->latitude,
            (float) $this->ugm->longitude
        );

        $this->assertGreaterThan(75.0, $distanceRegional);
        $this->assertLessThan(110.0, $distanceRegional);

        // Distance between Undip Semarang and UI Depok should be ~400 to 450 km
        $distanceInterCity = $this->service->calculateDistanceKm(
            (float) $this->undip->latitude,
            (float) $this->undip->longitude,
            (float) $this->ui->latitude,
            (float) $this->ui->longitude
        );

        $this->assertGreaterThan(380.0, $distanceInterCity);
        $this->assertLessThan(460.0, $distanceInterCity);
    }

    public function test_intra_campus_rate_calculation_is_cheap_and_fast(): void
    {
        $estimate = $this->service->calculateRate($this->undip, $this->undip, 'kampus');

        $this->assertEquals(0.0, $estimate['distance_km']);
        $this->assertEquals(CampusShippingRateService::TIER_INTRA_CAMPUS, $estimate['zone_tier']);
        $this->assertEquals(5000.00, $estimate['cost']);
        $this->assertEquals('Rp 5.000', $estimate['formatted_cost']);
        $this->assertTrue($estimate['rates']['kampus']['available']);
        $this->assertTrue($estimate['rates']['gosend']['available']);
        $this->assertEquals(8000.00, $estimate['rates']['gosend']['cost']);
    }

    public function test_same_city_inter_campus_rate_calculation(): void
    {
        $estimate = $this->service->calculateRate($this->undip, $this->unnes, 'kampus');

        $this->assertEquals(CampusShippingRateService::TIER_SAME_CITY, $estimate['zone_tier']);
        $this->assertEquals(8000.00, $estimate['cost']);
        $this->assertEquals('Rp 8.000', $estimate['formatted_cost']);
        $this->assertTrue($estimate['rates']['kampus']['available']);
        $this->assertTrue($estimate['rates']['gosend']['available']);
        $this->assertEquals(14000.00, $estimate['rates']['gosend']['cost']);
        $this->assertEquals(9000.00, $estimate['rates']['jnt']['cost']);
    }

    public function test_regional_inter_city_rate_calculation_disables_peer_courier(): void
    {
        $estimate = $this->service->calculateRate($this->undip, $this->ugm, 'jnt');

        $this->assertEquals(CampusShippingRateService::TIER_REGIONAL, $estimate['zone_tier']);
        $this->assertEquals(14000.00, $estimate['cost']);
        $this->assertFalse($estimate['rates']['kampus']['available']);
        $this->assertFalse($estimate['rates']['gosend']['available']);
        $this->assertTrue($estimate['rates']['jnt']['available']);
        $this->assertTrue($estimate['rates']['jne']['available']);
    }

    public function test_national_inter_city_rate_calculates_distance_tier(): void
    {
        $estimate = $this->service->calculateRate($this->undip, $this->ui, 'jnt');

        $this->assertEquals(CampusShippingRateService::TIER_INTER_CITY, $estimate['zone_tier']);
        $this->assertGreaterThanOrEqual(18000.00, $estimate['cost']);
        $this->assertFalse($estimate['rates']['kampus']['available']);
    }

    public function test_api_shipping_estimate_endpoint(): void
    {
        $response = $this->actingAs($this->buyer)->getJson(route('shipping.estimate', [
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->ugm->id,
            'courier' => 'jnt',
        ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'zone_tier' => CampusShippingRateService::TIER_REGIONAL,
                    'cost' => 14000.00,
                    'selected_courier' => 'jnt',
                ],
            ]);
    }

    public function test_api_shipping_estimate_validates_inputs(): void
    {
        $response = $this->actingAs($this->buyer)->getJson(route('shipping.estimate', [
            'origin_university_id' => 99999,
            'destination_university_id' => $this->ugm->id,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['origin_university_id']);
    }

    public function test_checkout_page_renders_campus_shipping_options(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('transactions.checkout', $this->product));

        $response->assertOk()
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Universitas Gadjah Mada')
            ->assertSee('destination_university_id')
            ->assertSee('Jarak Antar-Kampus');
    }

    public function test_checkout_stores_transaction_with_real_campus_shipping_rates(): void
    {
        $response = $this->actingAs($this->buyer)->post(route('transactions.store'), [
            'product_id' => $this->product->id,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->ugm->id,
            'courier_name' => 'jnt',
            'shipping_recipient_name' => 'Dewi Lestari',
            'shipping_phone' => '08987654321',
            'shipping_address' => 'Kos Pogung Baru F12, Sleman, Yogyakarta',
            'use_escrow' => 1,
        ]);

        $transaction = Transaction::latest()->first();

        $response->assertRedirect(route('transactions.show', $transaction));

        $this->assertNotNull($transaction);
        $this->assertEquals(Transaction::DELIVERY_METHOD_COURIER, $transaction->delivery_method);
        $this->assertEquals($this->undip->id, $transaction->origin_university_id);
        $this->assertEquals($this->ugm->id, $transaction->destination_university_id);
        $this->assertEquals('jnt', $transaction->courier_name);
        $this->assertEquals(14000.00, (float) $transaction->shipping_cost);
        $this->assertEquals(89000.00, (float) $transaction->total_amount); // 75.000 + 14.000
        $this->assertNotNull($transaction->distance_km);
        $this->assertGreaterThan(70.0, (float) $transaction->distance_km);

        // Check that escrow payment record mirrors the calculated shipping cost
        $this->assertNotNull($transaction->escrowPayment);
        $this->assertEquals(14000.00, (float) $transaction->escrowPayment->shipping_cost);
        $this->assertEquals(89000.00, (float) $transaction->escrowPayment->total_amount);
    }

    public function test_checkout_intra_campus_shipping_cost(): void
    {
        // Buyer at same campus (Undip) choosing kurir mahasiswa
        $buyerStudentUndip = Student::create([
            'university_id' => $this->undip->id,
            'student_number' => '24060122130099',
            'full_name' => 'Rian Hidayat',
            'major' => 'Statistika',
            'university_email' => 'rian.hidayat@students.undip.ac.id',
            'is_active' => true,
        ]);

        $buyerUndip = User::factory()->create([
            'name' => 'Rian Hidayat',
            'email' => 'rian.hidayat@students.undip.ac.id',
            'student_id' => $buyerStudentUndip->id,
        ]);

        $this->actingAs($buyerUndip)->post(route('transactions.store'), [
            'product_id' => $this->product->id,
            'payment_method' => Transaction::PAYMENT_METHOD_EWALLET,
            'payment_channel' => 'gopay',
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->undip->id,
            'courier_name' => 'kampus',
            'shipping_recipient_name' => 'Rian Hidayat',
            'shipping_phone' => '081234567899',
            'shipping_address' => 'Asrama Rusunawa Undip Kamar 204',
            'use_escrow' => 1,
        ]);

        $transaction = Transaction::latest()->first();

        $this->assertEquals(5000.00, (float) $transaction->shipping_cost);
        $this->assertEquals(80000.00, (float) $transaction->total_amount); // 75.000 + 5.000
    }

    public function test_transaction_show_page_displays_route_and_distance(): void
    {
        $transaction = Transaction::create([
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000.00,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->ugm->id,
            'distance_km' => 91.24,
            'estimated_delivery_days' => '1 - 2 Hari Kerja',
            'shipping_cost' => 14000.00,
            'total_amount' => 89000.00,
            'courier_name' => 'jnt',
            'shipping_recipient_name' => 'Dewi Lestari',
            'shipping_phone' => '08987654321',
            'shipping_address' => 'Pogung Baru F12, Sleman',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
            'status' => Transaction::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('transactions.show', $transaction));

        $response->assertOk()
            ->assertSee('Rute Pengiriman Antar-Kampus')
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Universitas Gadjah Mada')
            ->assertSee('91,2 km')
            ->assertSee('1 - 2 Hari Kerja')
            ->assertSee('Rp 14.000');
    }

    public function test_product_show_page_renders_shipping_estimator(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('products.show', $this->product));

        $response->assertOk()
            ->assertSee('Estimasi Ongkir ke Kampus Anda')
            ->assertSee('Universitas Diponegoro');
    }
}
