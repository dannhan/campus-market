<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBrowsingTest extends TestCase
{
    use RefreshDatabase;

    protected University $undip;

    protected University $ugm;

    protected User $undipSeller;

    protected User $ugmSeller;

    protected Category $bookCategory;

    protected Category $gadgetCategory;

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

        $this->undipSeller = $this->createVerifiedStudentUser($this->undip, '24060122130001', 'Budi Santoso', 'budi.santoso@students.undip.ac.id');
        $this->ugmSeller = $this->createVerifiedStudentUser($this->ugm, '24060122130002', 'Sari Wulandari', 'sari.wulandari@mail.ugm.ac.id');

        $this->bookCategory = Category::create(['name' => 'Buku per Semester', 'slug' => 'buku-per-semester']);
        $this->gadgetCategory = Category::create(['name' => 'Gadget Second', 'slug' => 'gadget-second']);

        Product::create($this->productAttributes($this->undipSeller, $this->bookCategory, [
            'title' => 'Buku Struktur Data Edisi Revisi',
            'description' => 'Bku teks struktur data untuk Informatika.',
            'price' => 75000,
            'condition' => 'used',
        ]));

        Product::create($this->productAttributes($this->undipSeller, $this->gadgetCategory, [
            'title' => 'Laptop Bekas Ram 8GB',
            'description' => 'Laptop kerja ringan untuk mahasiswa.',
            'price' => 3500000,
            'condition' => 'like_new',
        ]));

        Product::create($this->productAttributes($this->ugmSeller, $this->bookCategory, [
            'title' => 'Kalkulator Casio FX-991',
            'description' => 'Kalkulator untuk ujian kalkulus.',
            'price' => 180000,
            'condition' => 'used',
        ]));

        Product::create($this->productAttributes($this->ugmSeller, $this->gadgetCategory, [
            'title' => 'Sewa Proyektor Presentasi',
            'description' => 'Proyektor untuk sewa harian presentasi.',
            'price' => 50000,
            'condition' => 'new',
            'transaction_type' => 'rent',
        ]));

        Product::create($this->productAttributes($this->undipSeller, $this->bookCategory, [
            'title' => 'Buku Terjual Lama',
            'description' => 'Sudah terjual.',
            'price' => 20000,
            'status' => 'sold',
        ]));
    }

    protected function createVerifiedStudentUser(University $university, string $nim, string $name, string $email): User
    {
        $student = Student::create([
            'university_id' => $university->id,
            'student_number' => $nim,
            'full_name' => $name,
            'major' => 'Informatika',
            'university_email' => $email,
            'is_active' => true,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('Password123!'),
            'student_id' => $student->id,
            'email_verified_at' => now(),
        ]);
    }

    protected function productAttributes(User $seller, Category $category, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Produk',
            'slug' => Product::generateUniqueSlug('Produk '.uniqid()),
            'description' => 'Deskripsi produk.',
            'price' => 50000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
        ], $overrides);
    }

    public function test_guest_can_browse_active_products(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Buku Struktur Data Edisi Revisi');
        $response->assertSee('Laptop Bekas Ram 8GB');
        $response->assertDontSee('Buku Terjual Lama'); // Sold listings stay hidden
    }

    public function test_guest_can_view_active_product_detail(): void
    {
        $product = Product::where('title', 'Buku Struktur Data Edisi Revisi')->first();
        $initialViews = $product->views_count;

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Buku Struktur Data Edisi Revisi');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Universitas Diponegoro');
        $this->assertSame($initialViews + 1, $product->fresh()->views_count);
    }

    public function test_sold_product_detail_is_hidden_from_public_but_visible_to_owner(): void
    {
        $sold = Product::where('title', 'Buku Terjual Lama')->first();

        $this->get(route('products.show', $sold))->assertNotFound();

        $this->actingAs($sold->user)
            ->get(route('products.show', $sold))
            ->assertOk()
            ->assertSee('Buku Terjual Lama');
    }

    public function test_search_matches_title_and_description(): void
    {
        $this->get('/products?q='.urlencode('Struktur Data'))
            ->assertOk()
            ->assertSee('Buku Struktur Data Edisi Revisi')
            ->assertDontSee('Laptop Bekas Ram 8GB');

        // "ujian kalkulus" only appears in the Casio description
        $this->get('/products?q='.urlencode('ujian kalkulus'))
            ->assertOk()
            ->assertSee('Kalkulator Casio FX-991')
            ->assertDontSee('Buku Struktur Data Edisi Revisi');
    }

    public function test_filter_by_category(): void
    {
        $this->get('/products?category=gadget-second')
            ->assertOk()
            ->assertSee('Laptop Bekas Ram 8GB')
            ->assertSee('Sewa Proyektor Presentasi')
            ->assertDontSee('Buku Struktur Data Edisi Revisi');
    }

    public function test_filter_by_seller_university(): void
    {
        $this->get('/products?university=ugm')
            ->assertOk()
            ->assertSee('Kalkulator Casio FX-991')
            ->assertDontSee('Buku Struktur Data Edisi Revisi');
    }

    public function test_filter_by_condition_and_transaction_type(): void
    {
        $this->get('/products?condition=new')
            ->assertOk()
            ->assertSee('Sewa Proyektor Presentasi')
            ->assertDontSee('Laptop Bekas Ram 8GB');

        $this->get('/products?type=rent')
            ->assertOk()
            ->assertSee('Sewa Proyektor Presentasi')
            ->assertDontSee('Kalkulator Casio FX-991');
    }

    public function test_rental_price_suffix_is_rendered_as_html_not_escaped_text(): void
    {
        $response = $this->get('/products?type=rent');

        $response->assertOk()
            ->assertSee('<span class="text-[10px] font-semibold text-slate-400">/sewa</span>', false)
            ->assertDontSee('&lt;span class="text-[10px] font-semibold text-slate-400"&gt;/sewa&lt;/span&gt;', false);
    }

    public function test_filter_by_price_range(): void
    {
        $this->get('/products?min_price=100000&max_price=200000')
            ->assertOk()
            ->assertSee('Kalkulator Casio FX-991')
            ->assertDontSee('Laptop Bekas Ram 8GB')
            ->assertDontSee('Buku Struktur Data Edisi Revisi');
    }

    public function test_sort_by_price(): void
    {
        $response = $this->get('/products?sort=price_asc');
        $prices = $response->viewData('products')->getCollection()->pluck('price')->map(fn ($p) => (float) $p)->values();

        $this->assertTrue($prices->every(fn ($price, $i) => $i === 0 || $prices[$i - 1] <= $price));
    }

    public function test_invalid_filter_values_are_ignored(): void
    {
        $this->get('/products?condition=extra_terrestrial&type=trade')
            ->assertOk()
            ->assertSee('Buku Struktur Data Edisi Revisi');
    }

    public function test_product_grid_can_load_the_next_page_as_a_fragment(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            Product::create($this->productAttributes($this->undipSeller, $this->bookCategory, [
                'title' => 'Produk Tambahan '.$i,
                'description' => 'Produk tambahan untuk pengujian muat lebih banyak.',
                'price' => 300000 + $i,
            ]));
        }

        $firstPage = $this->getJson('/products?fragment=1&sort=price_asc');

        $firstPage->assertOk()
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('remaining', 1)
            ->assertJsonPath('count', 12);

        $this->assertStringContainsString('sort=price_asc', $firstPage->json('next_page_url'));
        $this->assertStringContainsString('Buku Struktur Data Edisi Revisi', $firstPage->json('html'));

        $secondPage = $this->getJson('/products?fragment=1&sort=price_asc&page=2');

        $secondPage->assertOk()
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('remaining', 0)
            ->assertJsonPath('count', 1);

        $this->assertStringContainsString('Laptop Bekas Ram 8GB', $secondPage->json('html'));
        $this->assertStringNotContainsString('Buku Struktur Data Edisi Revisi', $secondPage->json('html'));
    }
}
