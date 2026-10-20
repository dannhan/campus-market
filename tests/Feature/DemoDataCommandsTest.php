<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Database\Seeders\Demo\DemoProductSeeder;
use Database\Seeders\Demo\DemoUserSeeder;
use Database\Seeders\DemoMarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoDataCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_demo_seeder_creates_accounts_products_and_photos(): void
    {
        $this->seed(DemoMarketplaceSeeder::class);

        $demoUsers = User::where('email', 'like', DemoUserSeeder::EMAIL_PREFIX.'%@%')->get();

        $this->assertCount(count(DemoUserSeeder::USERS), $demoUsers);
        $this->assertTrue($demoUsers->every(
            fn (User $user) => $user->email_verified_at !== null && $user->student_id !== null
        ));
        $this->assertSame(
            count(DemoUserSeeder::USERS),
            Student::where('university_email', 'like', DemoUserSeeder::EMAIL_PREFIX.'%@%')->count()
        );
        $this->assertSame(count(DemoProductSeeder::PRODUCTS), Product::count());

        $firstProduct = Product::where('slug', DemoProductSeeder::PRODUCTS[0]['slug'])->firstOrFail();
        $cover = $firstProduct->images()->firstOrFail();

        $this->assertCount(1, $firstProduct->images->where('is_primary', true));
        $this->assertTrue($cover->is_primary);
        $this->assertSame(0, $cover->sort_order);
        $this->assertStringStartsWith('products/demo/', $cover->path);

        ProductImage::all()->each(
            fn (ProductImage $image) => Storage::disk('public')->assertExists($image->path)
        );
    }

    public function test_demo_seeder_can_be_run_repeatedly_without_duplicates(): void
    {
        $this->seed(DemoMarketplaceSeeder::class);

        $counts = [
            'users' => User::count(),
            'students' => Student::count(),
            'products' => Product::count(),
            'images' => ProductImage::count(),
        ];

        $this->seed(DemoMarketplaceSeeder::class);

        $this->assertSame($counts['users'], User::count());
        $this->assertSame($counts['students'], Student::count());
        $this->assertSame($counts['products'], Product::count());
        $this->assertSame($counts['images'], ProductImage::count());
    }

    public function test_demo_seed_command_populates_demo_marketplace(): void
    {
        $this->artisan('demo:seed')->assertSuccessful();

        $this->assertSame(
            count(DemoUserSeeder::USERS),
            User::where('email', 'like', DemoUserSeeder::EMAIL_PREFIX.'%@%')->count()
        );
        $this->assertSame(count(DemoProductSeeder::PRODUCTS), Product::count());
    }

    public function test_demo_clean_removes_only_demo_data(): void
    {
        $this->seed(DemoMarketplaceSeeder::class);

        $demoImage = ProductImage::firstOrFail();
        $demoEmail = DemoUserSeeder::USERS[0]['email'];

        $realUniversity = University::firstOrFail();
        $realStudent = Student::factory()->create(['university_id' => $realUniversity->id]);
        $realUser = User::factory()->forStudent($realStudent)->create();
        $realProduct = Product::factory()
            ->forUser($realUser)
            ->inCategory(Category::firstOrFail())
            ->create();

        $realPath = 'products/real-user-photo.jpg';
        Storage::disk('public')->put($realPath, 'real-photo');
        $realImage = $realProduct->images()->create([
            'path' => $realPath,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this->artisan('demo:clean')->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => $demoEmail]);
        $this->assertDatabaseMissing('students', ['university_email' => $demoEmail]);
        $this->assertDatabaseMissing('products', ['slug' => DemoProductSeeder::PRODUCTS[0]['slug']]);
        $this->assertDatabaseMissing('product_images', ['id' => $demoImage->id]);
        Storage::disk('public')->assertMissing($demoImage->path);

        $this->assertDatabaseHas('users', ['id' => $realUser->id]);
        $this->assertDatabaseHas('students', ['id' => $realStudent->id]);
        $this->assertDatabaseHas('products', ['id' => $realProduct->id]);
        $this->assertDatabaseHas('product_images', ['id' => $realImage->id]);
        Storage::disk('public')->assertExists($realPath);

        $this->assertDatabaseHas('categories', ['slug' => 'buku-per-semester']);
        $this->assertDatabaseHas('universities', ['slug' => 'undip']);
    }

    public function test_demo_clean_dry_run_changes_nothing(): void
    {
        $this->seed(DemoMarketplaceSeeder::class);

        $demoEmail = DemoUserSeeder::USERS[0]['email'];
        $demoImage = ProductImage::firstOrFail();

        $this->artisan('demo:clean', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => $demoEmail]);
        $this->assertSame(count(DemoProductSeeder::PRODUCTS), Product::count());
        $this->assertDatabaseHas('product_images', ['id' => $demoImage->id]);
        Storage::disk('public')->assertExists($demoImage->path);
    }
}
