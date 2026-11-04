<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected Product $activeProduct;

    protected Product $soldProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Admin CampusMarket',
            'email' => 'admin@campusmarket.ac.id',
            'email_verified_at' => now(),
        ]);

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

        $this->seller = User::factory()->forStudent($sellerStudent)->create([
            'is_admin' => false,
            'student_status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::factory()->forStudent($buyerStudent)->create([
            'is_admin' => false,
            'student_status' => 'alumni',
            'email_verified_at' => now(),
        ]);

        $category = Category::create([
            'name' => 'Buku per Semester',
            'slug' => 'buku-per-semester',
            'icon' => 'book-open',
            'sort_order' => 1,
        ]);

        $this->activeProduct = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Buku Fisika Dasar Halliday',
            'slug' => 'buku-fisika-dasar-halliday',
            'description' => 'Buku fisika jilid 1 edisi 10.',
            'price' => 120000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Tembalang',
            'bargainable' => true,
            'views_count' => 45,
        ]);

        $this->soldProduct = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Kalkulator Scientific Casio',
            'slug' => 'kalkulator-scientific-casio',
            'description' => 'Casio fx-991EX.',
            'price' => 250000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'sold',
            'location' => 'Kampus Tembalang',
            'bargainable' => false,
            'views_count' => 120,
        ]);

        // Accepted deal
        Offer::create([
            'product_id' => $this->activeProduct->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 100000,
            'message' => 'Boleh 100rb kak?',
            'status' => 'accepted',
        ]);

        // Pending offer
        Offer::create([
            'product_id' => $this->activeProduct->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 95000,
            'message' => 'Nego 95rb bisa?',
            'status' => 'pending',
        ]);

        // Conversation and message
        $conversation = Conversation::create([
            'product_id' => $this->activeProduct->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->buyer->id,
            'body' => 'Halo kak, bukunya masih?',
        ]);

        Wishlist::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->activeProduct->id,
        ]);
    }

    public function test_guest_cannot_access_transaction_reports(): void
    {
        $response = $this->get('/admin/reports');

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_student_user_is_forbidden_from_reports(): void
    {
        $response = $this->actingAs($this->seller)->get('/admin/reports');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_reports_and_statistics_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');

        $response->assertStatus(200);
        $response->assertSeeText('Laporan & Statistik');
        $response->assertSeeText('Ringkasan Metrik Penggunaan Platform');
        $response->assertSeeText('Estimasi Nilai Transaksi & Penawaran (GMV)');
        $response->assertSeeText('Laporan Riwayat Transaksi & Penawaran');
        $response->assertSeeText('Buku Fisika Dasar Halliday');
        $response->assertSeeText('Universitas Diponegoro');
        $response->assertSeeText('Universitas Gadjah Mada');
    }

    public function test_admin_sees_accurate_platform_statistics(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports');

        $response->assertStatus(200);

        // GMV: 250.000 (sold product) + 100.000 (accepted offer) = 350.000
        $response->assertSee('350.000');

        // Check user metrics
        $response->assertSee('Budi Santoso');
        $response->assertSee('Dewi Lestari');

        // Check offers metrics
        $response->assertSee('Diterima (1)');
        $response->assertSee('Pending (1)');
    }

    public function test_admin_can_filter_transaction_reports_by_status(): void
    {
        // Filter: accepted
        $responseAccepted = $this->actingAs($this->admin)->get('/admin/reports?status=accepted');
        $responseAccepted->assertStatus(200);
        $responseAccepted->assertSee('Sepakat / Diterima');

        // Filter: pending
        $responsePending = $this->actingAs($this->admin)->get('/admin/reports?status=pending');
        $responsePending->assertStatus(200);
        $responsePending->assertSee('Menunggu Respon');

        // Filter: rejected (should show empty state)
        $responseRejected = $this->actingAs($this->admin)->get('/admin/reports?status=rejected');
        $responseRejected->assertStatus(200);
        $responseRejected->assertSee('Tidak ada riwayat transaksi atau penawaran pada filter ini.');
    }
}
