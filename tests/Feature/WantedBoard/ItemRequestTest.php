<?php

namespace Tests\Feature\WantedBoard;

use App\Models\Category;
use App\Models\ItemRequest;
use App\Models\ItemRequestOffer;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $requester;

    protected User $seller;

    protected User $otherUser;

    protected Category $categoryBooks;

    protected Category $categoryElectronics;

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

        $requesterStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906001001',
            'full_name' => 'Rina Pembeli',
            'major' => 'Ilmu Komputer',
            'university_email' => 'rina@ui.ac.id',
            'is_active' => true,
        ]);

        $this->requester = User::factory()->create([
            'name' => 'Rina Pembeli',
            'email' => 'rina@ui.ac.id',
            'student_id' => $requesterStudent->id,
            'student_status' => 'active',
        ]);

        $sellerStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906002002',
            'full_name' => 'Budi Seller',
            'major' => 'Fisika',
            'university_email' => 'budi@ui.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Budi Seller',
            'email' => 'budi@ui.ac.id',
            'student_id' => $sellerStudent->id,
            'student_status' => 'active',
        ]);

        $otherStudent = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '1906003003',
            'full_name' => 'Doni Other',
            'major' => 'Hukum',
            'university_email' => 'doni@ui.ac.id',
            'is_active' => true,
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Doni Other',
            'email' => 'doni@ui.ac.id',
            'student_id' => $otherStudent->id,
            'student_status' => 'active',
        ]);

        $this->categoryBooks = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'icon' => 'book',
        ]);

        $this->categoryElectronics = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'icon' => 'device',
        ]);
    }

    public function test_guests_and_users_can_view_wanted_board_listings(): void
    {
        ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Buku Kalkulus Purcell Edisi 9',
            'slug' => 'dicari-buku-kalkulus-purcell-edisi-9',
            'description' => 'Dibutuhkan untuk kuliah kalkulus 1',
            'budget_min' => 30000,
            'budget_max' => 70000,
            'condition_preference' => 'used',
            'urgency' => 'urgent',
            'status' => 'open',
        ]);

        // Guest view
        $responseGuest = $this->get(route('requests.index'));
        $responseGuest->assertOk();
        $responseGuest->assertSee('Papan Cari Barang');
        $responseGuest->assertSee('Dicari Buku Kalkulus Purcell Edisi 9');
        $responseGuest->assertSee('Rp 30.000 - Rp 70.000');

        // Authenticated user view
        $responseAuth = $this->actingAs($this->seller)->get(route('requests.index'));
        $responseAuth->assertOk();
        $responseAuth->assertSee('Dicari Buku Kalkulus Purcell Edisi 9');
    }

    public function test_guests_cannot_create_item_request(): void
    {
        $this->get(route('requests.create'))->assertRedirect(route('login'));
        $this->post(route('requests.store'))->assertRedirect(route('login'));
    }

    public function test_authenticated_student_can_create_item_request(): void
    {
        $payload = [
            'title' => 'Dicari Modul Praktikum Algoritma',
            'category_id' => $this->categoryBooks->id,
            'description' => 'Membutuhkan modul praktikum algoritma semester 2, boleh bekas asalkan catatan tidak merusak tulisan.',
            'budget_min' => 20000,
            'budget_max' => 50000,
            'condition_preference' => 'used',
            'urgency' => 'urgent',
        ];

        $response = $this->actingAs($this->requester)->post(route('requests.store'), $payload);

        $created = ItemRequest::where('title', 'Dicari Modul Praktikum Algoritma')->firstOrFail();

        $response->assertRedirect(route('requests.show', $created));

        $this->assertDatabaseHas('item_requests', [
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Modul Praktikum Algoritma',
            'status' => 'open',
            'urgency' => 'urgent',
            'condition_preference' => 'used',
        ]);
    }

    public function test_requester_can_update_their_item_request(): void
    {
        $itemRequest = ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Buku Fisika Dasar Halliday',
            'slug' => 'dicari-buku-fisika-dasar-halliday',
            'description' => 'Fisika dasar edisi 10',
            'budget_min' => 50000,
            'budget_max' => 90000,
            'condition_preference' => 'any',
            'urgency' => 'normal',
            'status' => 'open',
        ]);

        $updatePayload = [
            'title' => 'Dicari Buku Fisika Dasar Halliday Jilid 1',
            'category_id' => $this->categoryBooks->id,
            'description' => 'Perlu jilid 1 segera',
            'budget_min' => 60000,
            'budget_max' => 100000,
            'condition_preference' => 'like_new',
            'urgency' => 'urgent',
        ];

        // Owner can update
        $response = $this->actingAs($this->requester)->put(route('requests.update', $itemRequest), $updatePayload);
        $response->assertRedirect(route('requests.show', $itemRequest));

        $this->assertDatabaseHas('item_requests', [
            'id' => $itemRequest->id,
            'title' => 'Dicari Buku Fisika Dasar Halliday Jilid 1',
            'budget_max' => 100000,
            'urgency' => 'urgent',
        ]);

        // Other user cannot update
        $responseOther = $this->actingAs($this->otherUser)->put(route('requests.update', $itemRequest), $updatePayload);
        $responseOther->assertForbidden();
    }

    public function test_requester_can_mark_request_as_fulfilled_or_closed_and_reopen(): void
    {
        $itemRequest = ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Buku Kalkulus Purcell',
            'slug' => 'dicari-buku-kalkulus-purcell',
            'description' => 'Kalkulus edisi 9',
            'budget_min' => 40000,
            'budget_max' => 70000,
            'condition_preference' => 'used',
            'urgency' => 'normal',
            'status' => 'open',
        ]);

        // 1. Mark as fulfilled (sudah ditemukan)
        $responseClose = $this->actingAs($this->requester)->post(route('requests.close', $itemRequest), [
            'status' => 'fulfilled',
        ]);
        $responseClose->assertRedirect();
        $this->assertEquals('fulfilled', $itemRequest->fresh()->status);
        $this->assertNotNull($itemRequest->fresh()->fulfilled_at);

        // 2. Reopen request
        $responseReopen = $this->actingAs($this->requester)->post(route('requests.reopen', $itemRequest));
        $responseReopen->assertRedirect();
        $this->assertEquals('open', $itemRequest->fresh()->status);
        $this->assertNull($itemRequest->fresh()->fulfilled_at);
    }

    public function test_other_student_can_submit_offer_to_open_item_request(): void
    {
        $itemRequest = ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryElectronics->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Kalkulator Casio FX-991EX',
            'slug' => 'dicari-kalkulator-casio-fx-991ex',
            'description' => 'Butuh kalkulator scientific untuk praktikum teknik',
            'budget_min' => 100000,
            'budget_max' => 200000,
            'condition_preference' => 'any',
            'urgency' => 'urgent',
            'status' => 'open',
        ]);

        // Create product owned by seller
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->categoryElectronics->id,
            'title' => 'Kalkulator Casio FX-991EX Bekas Kuliah',
            'slug' => 'kalkulator-casio-fx-991ex-bekas-kuliah',
            'description' => 'Fungsi 100% normal',
            'price' => 175000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
        ]);

        // Seller submits offer
        $offerPayload = [
            'price' => 170000,
            'message' => 'Halo kak, saya punya kalkulator Casio FX-991EX kondisi masih sangat bagus dan tombol empuk.',
            'product_id' => $product->id,
        ];

        $responseOffer = $this->actingAs($this->seller)->post(route('requests.offers.store', $itemRequest), $offerPayload);
        $responseOffer->assertRedirect();
        $responseOffer->assertSessionHas('status');

        $this->assertDatabaseHas('item_request_offers', [
            'item_request_id' => $itemRequest->id,
            'user_id' => $this->seller->id,
            'product_id' => $product->id,
            'price' => 170000,
            'status' => 'pending',
        ]);

        // Requester cannot offer on their own request
        $responseSelf = $this->actingAs($this->requester)->post(route('requests.offers.store', $itemRequest), $offerPayload);
        $responseSelf->assertSessionHas('error');
    }

    public function test_requester_can_accept_an_offer(): void
    {
        $itemRequest = ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Dicari Buku Algoritma Pemrograman',
            'slug' => 'dicari-buku-algoritma-pemrograman',
            'description' => 'Buku algoritma C++',
            'budget_min' => 30000,
            'budget_max' => 60000,
            'condition_preference' => 'used',
            'urgency' => 'normal',
            'status' => 'open',
        ]);

        $offer = ItemRequestOffer::create([
            'item_request_id' => $itemRequest->id,
            'user_id' => $this->seller->id,
            'product_id' => null,
            'price' => 50000,
            'message' => 'Saya ada bukunya kak, bisa COD besok di perpus.',
            'status' => 'pending',
        ]);

        // Requester accepts the offer
        $response = $this->actingAs($this->requester)->post(route('requests.offers.accept', [$itemRequest, $offer]));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertEquals('accepted', $offer->fresh()->status);
        $this->assertNotNull($offer->fresh()->accepted_at);
        $this->assertEquals('fulfilled', $itemRequest->fresh()->status);
        $this->assertNotNull($itemRequest->fresh()->fulfilled_at);
    }

    public function test_wanted_board_filters_by_category_urgency_and_search_query(): void
    {
        $req1 = ItemRequest::create([
            'user_id' => $this->requester->id,
            'category_id' => $this->categoryBooks->id,
            'university_id' => $this->university->id,
            'title' => 'Buku Aljabar Linear Elementary',
            'slug' => 'buku-aljabar-linear-elementary',
            'description' => 'Anton Rorres edisi 11',
            'budget_min' => 40000,
            'budget_max' => 80000,
            'condition_preference' => 'used',
            'urgency' => 'urgent',
            'status' => 'open',
        ]);

        $req2 = ItemRequest::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->categoryElectronics->id,
            'university_id' => $this->university->id,
            'title' => 'Arduino Uno R3 Kit',
            'slug' => 'arduino-uno-r3-kit',
            'description' => 'Kit mikrokontroler lengkap',
            'budget_min' => 150000,
            'budget_max' => 250000,
            'condition_preference' => 'like_new',
            'urgency' => 'low',
            'status' => 'open',
        ]);

        // Filter by category
        $responseCat = $this->get(route('requests.index', ['category' => 'buku-kuliah']));
        $responseCat->assertOk();
        $responseCat->assertSee('Buku Aljabar Linear Elementary');
        $responseCat->assertDontSee('Arduino Uno R3 Kit');

        // Filter by urgency
        $responseUrgent = $this->get(route('requests.index', ['urgency' => 'urgent']));
        $responseUrgent->assertOk();
        $responseUrgent->assertSee('Buku Aljabar Linear Elementary');
        $responseUrgent->assertDontSee('Arduino Uno R3 Kit');

        // Search query
        $responseSearch = $this->get(route('requests.index', ['q' => 'Arduino']));
        $responseSearch->assertOk();
        $responseSearch->assertSee('Arduino Uno R3 Kit');
        $responseSearch->assertDontSee('Buku Aljabar Linear Elementary');
    }
}
