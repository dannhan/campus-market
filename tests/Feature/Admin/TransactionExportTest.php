<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected Product $product;

    protected University $undip;

    protected University $ugm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undip = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->ugm = University::create([
            'name' => 'Universitas Gadjah Mada',
            'slug' => 'ugm',
            'email_domain' => 'mail.ugm.ac.id',
        ]);

        $this->admin = User::factory()->admin()->create([
            'name' => 'Administrator Campus',
            'email' => 'admin@campusmarket.ac.id',
            'email_verified_at' => now(),
        ]);

        $sellerStudent = Student::create([
            'university_id' => $this->undip->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $buyerStudent = Student::create([
            'university_id' => $this->ugm->id,
            'student_number' => '22/498765/TK/54321',
            'full_name' => 'Dewi Lestari',
            'major' => 'Teknik Industri',
            'university_email' => 'dewi.lestari@mail.ugm.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->forStudent($sellerStudent)->create([
            'name' => 'Budi Santoso',
            'is_admin' => false,
            'student_status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::factory()->forStudent($buyerStudent)->create([
            'name' => 'Dewi Lestari',
            'is_admin' => false,
            'student_status' => 'active',
            'email_verified_at' => now(),
        ]);

        $category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'icon' => 'book-open',
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Buku Pemrograman Python Lanjut',
            'slug' => 'buku-pemrograman-python-lanjut',
            'description' => 'Buku algoritma & pemrograman python lengkap.',
            'price' => 75000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus FSM Undip',
        ]);
    }

    public function test_admin_can_export_transactions_to_excel_xlsx(): void
    {
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-2026-XLSX01',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->ugm->id,
            'amount' => 75000,
            'shipping_cost' => 12000,
            'total_amount' => 87000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'courier_name' => 'jnt',
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_COMPLETED,
            'use_escrow' => true,
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'format' => 'xlsx',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan_transaksi_campusmarket_', $response->headers->get('Content-Disposition'));
        $this->assertStringEndsWith('.xlsx"', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringContainsString('xmlns="urn:schemas-microsoft-com:office:spreadsheet"', $content);
        $this->assertStringContainsString('<Worksheet ss:Name="Laporan Transaksi">', $content);
        $this->assertStringContainsString('Kode Transaksi', $content);
        $this->assertStringContainsString('TRX-2026-XLSX01', $content);
        $this->assertStringContainsString('Buku Pemrograman Python Lanjut', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('Dewi Lestari', $content);
        $this->assertStringContainsString('87000', $content);
    }

    public function test_admin_can_export_transactions_to_csv(): void
    {
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-2026-CSV01',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'origin_university_id' => $this->undip->id,
            'destination_university_id' => $this->ugm->id,
            'amount' => 75000,
            'shipping_cost' => 0,
            'total_amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_COMPLETED,
            'use_escrow' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'format' => 'csv',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan_transaksi_campusmarket_', $response->headers->get('Content-Disposition'));
        $this->assertStringEndsWith('.csv"', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        // Starts with UTF-8 BOM
        $this->assertTrue(str_starts_with($content, "\xEF\xBB\xBF"));
        $this->assertStringContainsString('Kode Transaksi', $content);
        $this->assertStringContainsString('TRX-2026-CSV01', $content);
        $this->assertStringContainsString('Buku Pemrograman Python Lanjut', $content);
        $this->assertStringContainsString('COD Kampus', $content);
    }

    public function test_admin_can_filter_transaction_export_by_status(): void
    {
        $completedTrx = Transaction::create([
            'transaction_code' => 'TRX-COMPLETED-99',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'total_amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $cancelledTrx = Transaction::create([
            'transaction_code' => 'TRX-CANCELLED-99',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'total_amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'status' => Transaction::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'format' => 'csv',
            'status' => Transaction::STATUS_COMPLETED,
        ]));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('TRX-COMPLETED-99', $content);
        $this->assertStringNotContainsString('TRX-CANCELLED-99', $content);
    }

    public function test_admin_can_filter_transaction_export_by_payment_method_and_escrow(): void
    {
        $escrowBankTrx = Transaction::create([
            'transaction_code' => 'TRX-ESCROW-BANK',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 100000,
            'total_amount' => 100000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COURIER,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_channel' => 'bca',
            'use_escrow' => true,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        $directCodTrx = Transaction::create([
            'transaction_code' => 'TRX-DIRECT-COD',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 50000,
            'total_amount' => 50000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'payment_channel' => 'cod',
            'use_escrow' => false,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'format' => 'csv',
            'payment_method' => 'bank_transfer',
            'use_escrow' => '1',
        ]));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('TRX-ESCROW-BANK', $content);
        $this->assertStringNotContainsString('TRX-DIRECT-COD', $content);
    }

    public function test_admin_can_filter_transaction_export_by_keyword_search(): void
    {
        $targetTrx = Transaction::create([
            'transaction_code' => 'TRX-KEYWORD-TARGET',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'total_amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $otherTrx = Transaction::create([
            'transaction_code' => 'TRX-OTHER-IGNORE',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 75000,
            'total_amount' => 75000,
            'delivery_method' => Transaction::DELIVERY_METHOD_COD,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'format' => 'csv',
            'q' => 'KEYWORD-TARGET',
        ]));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('TRX-KEYWORD-TARGET', $content);
        $this->assertStringNotContainsString('TRX-OTHER-IGNORE', $content);
    }

    public function test_non_admin_cannot_export_transactions(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('admin.reports.export', [
            'format' => 'xlsx',
        ]));

        $response->assertForbidden();
    }

    public function test_guest_cannot_export_transactions(): void
    {
        $response = $this->get(route('admin.reports.export', [
            'format' => 'xlsx',
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_reports_page_displays_export_button_and_filters(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Ekspor Laporan Transaksi ke Excel');
        $response->assertSee('Unduh Excel');
        $response->assertSee('Download File Laporan');
        $response->assertSee(route('admin.reports.export'));
    }
}
