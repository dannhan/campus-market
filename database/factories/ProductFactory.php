<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Buku Struktur Data Edisi Revisi',
            'Breadboard dan Komponen Praktikum',
            'Laptop Bekas Ram 8GB',
            'Jasa Les Privat Kalkulus',
            'Kalkulator Casio FX-991',
            'Jaket Lab Teknik',
        ]);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Product::generateUniqueSlug($title . ' ' . fake()->unique()->numerify('#####')),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(10, 750) * 1000,
            'condition' => fake()->randomElement(['new', 'like_new', 'used']),
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => fake()->randomElement(['Kampus Tembalang', 'Kampus Pleburan', 'Kampus Siscor']),
            'bargainable' => true,
            'views_count' => 0,
        ];
    }

    /**
     * Attach the product to a specific seller.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    /**
     * Place the product in a specific category.
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }

    /**
     * Mark the listing as already sold.
     */
    public function sold(): static
    {
        return $this->state(fn () => ['status' => 'sold']);
    }

    /**
     * Mark the listing as rent-only.
     */
    public function forRent(): static
    {
        return $this->state(fn () => ['transaction_type' => 'rent']);
    }

    /**
     * Disable bargaining on the listing.
     */
    public function nonBargainable(): static
    {
        return $this->state(fn () => ['bargainable' => false]);
    }
}
