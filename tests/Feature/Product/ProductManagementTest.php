<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $seller;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $student = Student::create([
            'university_id' => $university->id,
            'student_number' => '24060122130001',
            'full_name' => 'Budi Santoso',
            'major' => 'Informatika',
            'university_email' => 'budi.santoso@students.undip.ac.id',
            'is_active' => true,
        ]);

        $this->seller = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@students.undip.ac.id',
            'password' => bcrypt('Password123!'),
            'student_id' => $student->id,
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Buku per Semester',
            'slug' => 'buku-per-semester',
        ]);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Buku Struktur Data Edisi Revisi',
            'description' => 'Buku bekas layak pakai, minim highlight.',
            'category_id' => $this->category->id,
            'price' => 75000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'location' => 'Kampus Tembalang',
            'bargainable' => true,
        ], $overrides);
    }

    public function test_guest_cannot_access_product_creation_form(): void
    {
        $this->get('/products/create')->assertRedirect('/login');
    }

    public function test_product_form_submits_photos_natively_without_turbo(): void
    {
        $response = $this->actingAs($this->seller)->get('/products/create');

        $response->assertOk();
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('data-turbo="false"', false);
    }

    public function test_verified_user_can_publish_product_with_photos(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->seller)->post('/products', $this->validPayload([
            'images' => [UploadedFile::fake()->image('buku.jpg'), UploadedFile::fake()->image('isi-buku.png')],
        ]));

        $product = Product::where('title', 'Buku Struktur Data Edisi Revisi')->first();

        $response->assertRedirect(route('products.show', $product));
        $response->assertSessionHas('status');

        $this->assertNotNull($product);
        $this->assertSame('active', $product->status);
        $this->assertSame($this->seller->id, $product->user_id);
        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images->first()->is_primary);
        $this->assertSame(0, $product->images->first()->sort_order);
        $this->assertSame(1, $product->images->last()->sort_order);

        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    public function test_product_creation_requires_valid_fields(): void
    {
        $response = $this->actingAs($this->seller)->post('/products', $this->validPayload([
            'title' => '',
            'price' => 500,
            'condition' => 'rusted',
            'category_id' => 99999,
        ]));

        $response->assertSessionHasErrors(['title', 'price', 'condition', 'category_id']);
        $this->assertSame(0, Product::count());
    }

    public function test_product_creation_rejects_more_than_five_images(): void
    {
        $images = array_map(fn () => UploadedFile::fake()->image('foto.jpg'), range(1, 6));

        $response = $this->actingAs($this->seller)->post('/products', $this->validPayload(['images' => $images]));

        $response->assertSessionHasErrors('images');
        $this->assertSame(0, Product::count());
    }

    public function test_product_creation_rejects_images_larger_than_five_megabytes(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->seller)->post('/products', $this->validPayload([
            'images' => [UploadedFile::fake()->image('foto-besar.jpg')->size(6144)],
        ]));

        $response->assertSessionHasErrors('images.0');
        $this->assertSame(0, Product::count());
    }

    public function test_owner_can_edit_product(): void
    {
        $product = Product::create($this->validPayload(['user_id' => $this->seller->id]));

        $response = $this->actingAs($this->seller)->put(route('products.update', $product), $this->validPayload([
            'title' => 'Buku Struktur Data Edisi Baru',
            'price' => 90000,
        ]));

        $response->assertRedirect(route('products.show', $product->fresh()));
        $this->assertSame('Buku Struktur Data Edisi Baru', $product->fresh()->title);
        $this->assertEquals(90000, $product->fresh()->price);
    }

    public function test_non_owner_cannot_edit_or_delete_product(): void
    {
        $product = Product::create($this->validPayload(['user_id' => $this->seller->id]));

        $intruder = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($intruder)
            ->put(route('products.update', $product), $this->validPayload(['title' => 'Diretas']))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('products.destroy', $product))
            ->assertForbidden();

        $this->assertSame('Buku Struktur Data Edisi Revisi', $product->fresh()->title);
    }

    public function test_owner_can_delete_product_and_stored_photos_are_removed(): void
    {
        Storage::fake('public');

        $product = Product::create($this->validPayload(['user_id' => $this->seller->id]));
        $image = $product->images()->create([
            'path' => UploadedFile::fake()->image('cover.jpg')->store('products', 'public'),
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->seller)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_editing_product_with_new_photos_replaces_old_files(): void
    {
        Storage::fake('public');

        $product = Product::create($this->validPayload(['user_id' => $this->seller->id]));
        $oldImage = $product->images()->create([
            'path' => UploadedFile::fake()->image('lama.jpg')->store('products', 'public'),
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->seller)->put(route('products.update', $product), $this->validPayload([
            'images' => [UploadedFile::fake()->image('baru.jpg')],
        ]));

        $response->assertRedirect(route('products.show', $product->fresh()));

        $product->refresh()->load('images');
        $this->assertCount(1, $product->images);
        $this->assertNotSame($oldImage->path, $product->images->first()->path);
        Storage::disk('public')->assertMissing($oldImage->path);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }
}
