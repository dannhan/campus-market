<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisputeResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected User $otherStudent;

    protected Product $product;

    protected Offer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Administrator CampusMarket',
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
        ]);

        $this->buyer = User::factory()->forStudent($buyerStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->otherStudent = User::factory()->forStudent($otherStudent)->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'description' => 'Buku pelajaran dan diktat kuliah',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Buku Kalkulus Purcell Edisi 9',
            'slug' => 'buku-kalkulus-purcell-edisi-9',
            'description' => 'Kondisi mulus tanpa coretan pensil.',
            'price' => 75000,
            'condition' => 'like_new',
            'status' => 'sold',
        ]);

        $this->offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 70000,
            'message' => 'Bisa COD di perpustakaan pusat?',
            'status' => 'accepted',
        ]);
    }

    public function test_guest_cannot_access_dispute_routes(): void
    {
        $this->get(route('disputes.index'))->assertRedirect(route('login'));
        $this->get(route('disputes.create'))->assertRedirect(route('login'));
        $this->get(route('admin.disputes.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_dispute_endpoints(): void
    {
        $dispute = Dispute::create([
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Buku tidak sesuai deskripsi',
            'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
            'description' => 'Ada banyak halaman sobek pada bab integral.',
            'solution_requested' => Dispute::SOLUTION_REFUND,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $this->actingAs($this->buyer)
            ->get(route('admin.disputes.index'))
            ->assertForbidden();

        $this->actingAs($this->buyer)
            ->get(route('admin.disputes.show', $dispute))
            ->assertForbidden();

        $this->actingAs($this->buyer)
            ->put(route('admin.disputes.update', $dispute), [
                'status' => Dispute::STATUS_RESOLVED,
                'admin_decision' => Dispute::DECISION_FAVOR_BUYER,
                'admin_notes' => 'Catatan mediasi',
            ])
            ->assertForbidden();
    }

    public function test_student_can_view_their_disputes_index(): void
    {
        Dispute::create([
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Buku tidak sesuai deskripsi',
            'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
            'description' => 'Ada banyak halaman sobek.',
            'solution_requested' => Dispute::SOLUTION_REFUND,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $response = $this->actingAs($this->buyer)
            ->get(route('disputes.index'));

        $response->assertOk();
        $response->assertSeeText('Pusat Resolusi Kendala');
        $response->assertSeeText('Buku tidak sesuai deskripsi');
    }

    public function test_buyer_can_create_dispute_for_their_transaction(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('disputes.store'), [
                'product_id' => $this->product->id,
                'offer_id' => $this->offer->id,
                'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
                'solution_requested' => Dispute::SOLUTION_REFUND,
                'title' => 'Kondisi barang cacat tersembunyi',
                'description' => 'Cover buku terlepas dari jilidannya saat diterima saat COD.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('disputes', [
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Kondisi barang cacat tersembunyi',
            'status' => Dispute::STATUS_OPEN,
        ]);
    }

    public function test_seller_can_create_dispute_for_buyer_non_payment(): void
    {
        $response = $this->actingAs($this->seller)
            ->post(route('disputes.store'), [
                'product_id' => $this->product->id,
                'offer_id' => $this->offer->id,
                'reason' => Dispute::REASON_PAYMENT_ISSUE,
                'solution_requested' => Dispute::SOLUTION_CANCEL_DEAL,
                'title' => 'Pembeli tidak mentransfer sisa pembayaran',
                'description' => 'Pembeli berjanji bayar lewat transfer setelah COD tapi tidak ada konfirmasi.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('disputes', [
            'product_id' => $this->product->id,
            'reporter_id' => $this->seller->id,
            'respondent_id' => $this->buyer->id,
            'status' => Dispute::STATUS_OPEN,
        ]);
    }

    public function test_unrelated_user_cannot_create_dispute_for_others_transaction(): void
    {
        $response = $this->actingAs($this->otherStudent)
            ->post(route('disputes.store'), [
                'product_id' => $this->product->id,
                'offer_id' => $this->offer->id,
                'reason' => Dispute::REASON_OTHER,
                'solution_requested' => Dispute::SOLUTION_OTHER,
                'title' => 'Mencoba ikut campur transaksi orang lain',
                'description' => 'Deskripsi sengketa yang tidak valid.',
            ]);

        $response->assertForbidden();
    }

    public function test_dispute_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('disputes.store'), [
                'product_id' => $this->product->id,
                'title' => '',
                'reason' => 'invalid_reason',
                'description' => 'pendek',
                'solution_requested' => 'invalid_solution',
            ]);

        $response->assertSessionHasErrors(['title', 'reason', 'description', 'solution_requested']);
    }

    public function test_admin_can_view_disputes_index_and_filter_by_status(): void
    {
        $openDispute = Dispute::create([
            'product_id' => $this->product->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Sengketa Terbuka',
            'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
            'description' => 'Keterangan sengketa pertama.',
            'solution_requested' => Dispute::SOLUTION_REFUND,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $resolvedDispute = Dispute::create([
            'product_id' => $this->product->id,
            'reporter_id' => $this->seller->id,
            'respondent_id' => $this->buyer->id,
            'title' => 'Sengketa Sudah Selesai',
            'reason' => Dispute::REASON_PAYMENT_ISSUE,
            'description' => 'Keterangan sengketa kedua.',
            'solution_requested' => Dispute::SOLUTION_CANCEL_DEAL,
            'status' => Dispute::STATUS_RESOLVED,
            'admin_id' => $this->admin->id,
            'admin_decision' => Dispute::DECISION_FAVOR_SELLER,
            'admin_notes' => 'Telah diselesaikan.',
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.disputes.index', ['status' => 'open']));

        $response->assertOk();
        $response->assertSeeText('Sengketa Terbuka');
        $response->assertDontSeeText('Sengketa Sudah Selesai');
    }

    public function test_admin_can_view_dispute_detail(): void
    {
        $dispute = Dispute::create([
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Barang Tidak Sesuai Spesifikasi',
            'reason' => Dispute::REASON_NOT_AS_DESCRIBED,
            'description' => 'Ada halaman yang robek parah.',
            'solution_requested' => Dispute::SOLUTION_REFUND,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.disputes.show', $dispute));

        $response->assertOk();
        $response->assertSeeText('Barang Tidak Sesuai Spesifikasi');
        $response->assertSeeText($this->seller->name);
        $response->assertSeeText($this->buyer->name);
        $response->assertSeeText('Keputusan Mediasi Administrator');
    }

    public function test_admin_can_resolve_dispute_with_decision_and_notes(): void
    {
        $dispute = Dispute::create([
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Masalah Pembayaran COD',
            'reason' => Dispute::REASON_PAYMENT_ISSUE,
            'description' => 'Pembeli menolak membayar harga yang telah disepakati.',
            'solution_requested' => Dispute::SOLUTION_CANCEL_DEAL,
            'status' => Dispute::STATUS_OPEN,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.disputes.update', $dispute), [
                'status' => Dispute::STATUS_RESOLVED,
                'admin_decision' => Dispute::DECISION_FAVOR_SELLER,
                'admin_notes' => 'Pembeli terbukti wanprestasi. Transaksi dibatalkan dan barang tetap menjadi hak penjual.',
            ]);

        $response->assertRedirect(route('admin.disputes.show', $dispute));
        $response->assertSessionHas('status');

        $dispute->refresh();
        $this->assertSame(Dispute::STATUS_RESOLVED, $dispute->status);
        $this->assertSame(Dispute::DECISION_FAVOR_SELLER, $dispute->admin_decision);
        $this->assertSame('Pembeli terbukti wanprestasi. Transaksi dibatalkan dan barang tetap menjadi hak penjual.', $dispute->admin_notes);
        $this->assertSame($this->admin->id, $dispute->admin_id);
        $this->assertNotNull($dispute->resolved_at);
    }

    public function test_both_parties_can_view_dispute_and_admin_verdict(): void
    {
        $dispute = Dispute::create([
            'product_id' => $this->product->id,
            'offer_id' => $this->offer->id,
            'reporter_id' => $this->buyer->id,
            'respondent_id' => $this->seller->id,
            'title' => 'Sengketa Kesepakatan Akhir',
            'reason' => Dispute::REASON_OTHER,
            'description' => 'Kedua belah pihak sepakat minta mediasi admin kampus.',
            'solution_requested' => Dispute::SOLUTION_OTHER,
            'status' => Dispute::STATUS_RESOLVED,
            'admin_id' => $this->admin->id,
            'admin_decision' => Dispute::DECISION_MUTUAL_AGREEMENT,
            'admin_notes' => 'Kedua belah pihak sepakat retur barang dan refund 50%.',
            'resolved_at' => now(),
        ]);

        // Buyer (Reporter) can view
        $responseBuyer = $this->actingAs($this->buyer)
            ->get(route('disputes.show', $dispute));
        $responseBuyer->assertOk();
        $responseBuyer->assertSeeText('Keputusan Resmi Mediasi Administrator');
        $responseBuyer->assertSeeText('Kedua belah pihak sepakat retur barang dan refund 50%.');

        // Seller (Respondent) can view
        $responseSeller = $this->actingAs($this->seller)
            ->get(route('disputes.show', $dispute));
        $responseSeller->assertOk();
        $responseSeller->assertSeeText('Keputusan Resmi Mediasi Administrator');

        // Third-party student cannot view (403)
        $this->actingAs($this->otherStudent)
            ->get(route('disputes.show', $dispute))
            ->assertForbidden();
    }
}
