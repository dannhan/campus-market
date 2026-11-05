<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkProductUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected Category $categoryBooks;

    protected Category $categoryLab;

    protected Category $categoryElectronics;

    protected University $university;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'undip.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
        ]);

        $student = Student::create([
            'university_id' => $this->university->id,
            'student_number' => '24060122130099',
            'full_name' => 'Budi Seller',
            'major' => 'Informatika',
            'university_email' => 'budi.seller@undip.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Budi Seller',
            'email' => 'budi.seller@undip.ac.id',
            'student_id' => $student->id,
            'student_status' => 'active',
        ]);

        $this->categoryBooks = Category::create([
            'name' => 'Buku per Semester',
            'slug' => 'buku-per-semester',
            'icon' => 'book',
        ]);

        $this->categoryLab = Category::create([
            'name' => 'Perlengkapan Lab',
            'slug' => 'perlengkapan-lab',
            'icon' => 'beaker',
        ]);

        $this->categoryElectronics = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'icon' => 'device',
        ]);
    }

    public function test_guests_cannot_access_bulk_upload_or_download_template(): void
    {
        $this->get(route('seller.products.bulk'))->assertRedirect(route('login'));
        $this->get(route('seller.products.bulk.template'))->assertRedirect(route('login'));
        $this->post(route('seller.products.bulk.store'))->assertRedirect(route('login'));
    }

    public function test_seller_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->seller)->get(route('seller.products.bulk.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('template_bulk_upload_produk.csv', (string) $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringContainsString('title,category,price,condition,transaction_type,bargainable,location,description,delivery_methods,image_url', $content);
        $this->assertStringContainsString('Buku Kalkulus Purcell', $content);
        $this->assertStringContainsString('Buku per Semester', $content);
    }

    public function test_seller_can_view_bulk_upload_page(): void
    {
        $response = $this->actingAs($this->seller)->get(route('seller.products.bulk'));

        $response->assertOk();
        $response->assertSee('Upload Massal Produk');
        $response->assertSee('Unduh Template CSV');
        $response->assertSee('Buku per Semester');
        $response->assertSee('Perlengkapan Lab');
        $response->assertSee('Elektronik');
    }

    public function test_seller_can_bulk_upload_products_from_csv_with_comma_delimiter(): void
    {
        $csvData = implode("\n", [
            'title,category,price,condition,transaction_type,bargainable,location,description,delivery_methods,image_url',
            '"Kalkulus Edisi 9","Buku per Semester","65000","bekas","jual","ya","Kampus Tembalang","Buku kalkulus lengkap","pickup_point,courier","https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c"',
            '"Jas Lab Kimia L","perlengkapan-lab","Rp 50.000","seperti_baru","jual","tidak","Labtek FSM","Jas lab bersih","pickup_point",""',
            '"Kalkulator Casio FX","'.$this->categoryElectronics->id.'","185000","baru","jual","ya","Perpus Pusat","Kalkulator teknik","cod,courier",""',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvData);

        $response = $this->actingAs($this->seller)->post(route('seller.products.bulk.store'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('seller.products'));
        $response->assertSessionHas('status', 'Berhasil mengunggah 3 produk ke etalase toko Anda.');

        $this->assertDatabaseHas('products', [
            'user_id' => $this->seller->id,
            'title' => 'Kalkulus Edisi 9',
            'category_id' => $this->categoryBooks->id,
            'price' => 65000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'bargainable' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'user_id' => $this->seller->id,
            'title' => 'Jas Lab Kimia L',
            'category_id' => $this->categoryLab->id,
            'price' => 50000,
            'condition' => 'like_new',
            'bargainable' => false,
        ]);

        $this->assertDatabaseHas('products', [
            'user_id' => $this->seller->id,
            'title' => 'Kalkulator Casio FX',
            'category_id' => $this->categoryElectronics->id,
            'price' => 185000,
            'condition' => 'new',
            'bargainable' => true,
        ]);

        // Check image was attached for the 1st product
        $firstProduct = Product::where('title', 'Kalkulus Edisi 9')->firstOrFail();
        $this->assertTrue($firstProduct->images()->where('path', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c')->exists());
    }

    public function test_seller_can_bulk_upload_products_from_semicolon_delimited_csv(): void
    {
        // Indonesian / European Excel standard uses semicolon
        $csvData = implode("\n", [
            'title;category;price;condition;transaction_type;bargainable;location;description;delivery_methods;image_url',
            '"Modul Praktikum Fisika";"Buku per Semester";"35000";"bekas";"jual";"1";"FSM";"Modul bersih";"pickup_point";""',
            '"Multimeter Digital";"Elektronik";"120000";"seperti baru";"jual";"1";"Gedung E";"Multimeter normal";"courier";""',
        ]);

        $file = UploadedFile::fake()->createWithContent('products_id_excel.csv', $csvData);

        $response = $this->actingAs($this->seller)->post(route('seller.products.bulk.store'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('seller.products'));
        $response->assertSessionHas('status', 'Berhasil mengunggah 2 produk ke etalase toko Anda.');

        $this->assertDatabaseHas('products', [
            'user_id' => $this->seller->id,
            'title' => 'Modul Praktikum Fisika',
            'price' => 35000,
        ]);

        $this->assertDatabaseHas('products', [
            'user_id' => $this->seller->id,
            'title' => 'Multimeter Digital',
            'price' => 120000,
            'condition' => 'like_new',
        ]);
    }

    public function test_bulk_upload_validates_required_columns_and_rejects_missing_headers(): void
    {
        // Missing category and price
        $csvData = implode("\n", [
            'title,description,location',
            '"Barang A","Deskripsi A","Semarang"',
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid_headers.csv', $csvData);

        $response = $this->actingAs($this->seller)->post(route('seller.products.bulk.store'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('bulk_errors');

        $this->assertEquals(0, Product::count());
    }

    public function test_bulk_upload_handles_partial_failures_and_reports_row_errors(): void
    {
        $csvData = implode("\n", [
            'title,category,price,condition',
            '"Produk Valid","Buku per Semester","50000","bekas"',
            '"Produk Kategori Salah","Kategori Fiktif Yang Tidak Ada","40000","bekas"',
            '"Produk Harga Murahan","Elektronik","500","bekas"', // price < 1000
        ]);

        $file = UploadedFile::fake()->createWithContent('partial_error.csv', $csvData);

        $response = $this->actingAs($this->seller)->post(route('seller.products.bulk.store'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('seller.products'));
        $response->assertSessionHas('status', 'Berhasil mengunggah 1 produk ke etalase toko Anda.');
        $response->assertSessionHas('bulk_warnings');

        $warnings = session('bulk_warnings');
        $this->assertCount(2, $warnings);
        $this->assertStringContainsString('Baris 3: Kategori \'Kategori Fiktif Yang Tidak Ada\' tidak ditemukan', $warnings[0]);
        $this->assertStringContainsString('Baris 4: Harga produk minimal Rp 1.000', $warnings[1]);

        $this->assertDatabaseHas('products', [
            'title' => 'Produk Valid',
        ]);
        $this->assertDatabaseMissing('products', [
            'title' => 'Produk Kategori Salah',
        ]);
    }

    public function test_bulk_upload_rejects_invalid_file_extension(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->seller)->post(route('seller.products.bulk.store'), [
            'csv_file' => $file,
        ]);

        $response->assertSessionHasErrors('csv_file');
        $this->assertEquals(0, Product::count());
    }
}
