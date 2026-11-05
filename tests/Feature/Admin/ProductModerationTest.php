<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $seller;

    private User $buyer;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->seller = User::factory()->create();
        $this->buyer = User::factory()->create();
        $this->category = Category::factory()->create(['name' => 'Buku Kuliah']);
    }

    public function test_admin_can_access_moderation_dashboard_and_view_listings(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Kalkulus Purcell Edisi 9',
            'status' => 'active',
            'moderation_status' => Product::MODERATION_STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.moderation.index'));

        $response->assertStatus(200);
        $response->assertSee('Moderasi &amp; Verifikasi Produk', false);
        $response->assertSee('Kalkulus Purcell Edisi 9');
    }

    public function test_non_admin_cannot_access_moderation_dashboard(): void
    {
        $response = $this->actingAs($this->seller)->get(route('admin.moderation.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_verify_product_and_award_badge(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Mikroskop Binokuler Lab',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.verify', $product), [
            'notes' => 'Telah diverifikasi sesuai standar alat praktikum.',
        ]);

        $response->assertSessionHas('status');
        $this->assertTrue($product->fresh()->is_verified);
        $this->assertEquals(Product::MODERATION_STATUS_APPROVED, $product->fresh()->moderation_status);

        // Verify badge is rendered on product detail page
        $responseDetail = $this->get(route('products.show', $product));
        $responseDetail->assertSee('Produk Terverifikasi Kampus');
    }

    public function test_admin_can_revoke_product_verification(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.unverify', $product));

        $response->assertSessionHas('status');
        $this->assertFalse($product->fresh()->is_verified);
    }

    public function test_admin_can_reject_product_with_violation_reason(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'title' => 'Produk Mencurigakan Indikasi Scam',
            'status' => 'active',
            'moderation_status' => Product::MODERATION_STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.reject', $product), [
            'reason' => 'Harga tidak wajar dan foto mengambil dari internet tanpa izin.',
        ]);

        $response->assertSessionHas('status');
        $this->assertEquals(Product::MODERATION_STATUS_REJECTED, $product->fresh()->moderation_status);
        $this->assertEquals('Harga tidak wajar dan foto mengambil dari internet tanpa izin.', $product->fresh()->moderation_notes);

        $this->flushSession();

        // Catalog search should NOT include rejected products
        $responseCatalog = $this->get(route('products.index'));
        $responseCatalog->assertDontSee('Produk Mencurigakan Indikasi Scam');

        // Other students attempting to view rejected product should receive 404
        $responseOtherStudent = $this->actingAs($this->buyer)->get(route('products.show', $product));
        $responseOtherStudent->assertStatus(404);

        // But owner can view it and see the rejection warning
        $responseOwner = $this->actingAs($this->seller)->get(route('products.show', $product));
        $responseOwner->assertStatus(200);
        $responseOwner->assertSee('Produk Ditangguhkan oleh Tim Moderator');
        $responseOwner->assertSee('Harga tidak wajar dan foto mengambil dari internet tanpa izin.');
    }

    public function test_admin_can_approve_previously_rejected_product(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'title' => 'Buku Sastra yang Sudah Diperbaiki',
            'status' => 'active',
            'moderation_status' => Product::MODERATION_STATUS_REJECTED,
            'moderation_notes' => 'Perlu revisi deskripsi.',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.approve', $product));

        $response->assertSessionHas('status');
        $this->assertEquals(Product::MODERATION_STATUS_APPROVED, $product->fresh()->moderation_status);
        $this->assertNull($product->fresh()->moderation_notes);

        // Now appears in catalog
        $responseCatalog = $this->get(route('products.index'));
        $responseCatalog->assertSee('Buku Sastra yang Sudah Diperbaiki');
    }

    public function test_student_can_report_suspicious_product(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'moderation_status' => Product::MODERATION_STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->buyer)->post(route('products.report', $product), [
            'reason' => 'scam_fraud',
            'details' => 'Penjual meminta transfer ke rekening pribadi tanpa escrow.',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('product_reports', [
            'product_id' => $product->id,
            'reporter_id' => $this->buyer->id,
            'reason' => 'scam_fraud',
            'details' => 'Penjual meminta transfer ke rekening pribadi tanpa escrow.',
            'status' => ProductReport::STATUS_PENDING,
        ]);

        // Product should be automatically flagged
        $this->assertEquals(Product::MODERATION_STATUS_FLAGGED, $product->fresh()->moderation_status);
    }

    public function test_student_cannot_report_own_product(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
        ]);

        $response = $this->actingAs($this->seller)->post(route('products.report', $product), [
            'reason' => 'scam_fraud',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('product_reports', [
            'product_id' => $product->id,
        ]);
    }

    public function test_admin_can_dismiss_student_report(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'moderation_status' => Product::MODERATION_STATUS_FLAGGED,
        ]);

        $report = ProductReport::factory()->create([
            'product_id' => $product->id,
            'reporter_id' => $this->buyer->id,
            'status' => ProductReport::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.reports.dismiss', [$product, $report]));

        $response->assertSessionHas('status');
        $this->assertEquals(ProductReport::STATUS_DISMISSED, $report->fresh()->status);
        $this->assertEquals(Product::MODERATION_STATUS_APPROVED, $product->fresh()->moderation_status);
    }

    public function test_admin_can_permanently_delete_violating_product(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->seller->id,
            'title' => 'Barang Ilegal Kampus',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.moderation.destroy', $product));

        $response->assertRedirect(route('admin.moderation.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
