<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Buku per Semester',
            'Perlengkapan Lab',
            'Gadget Second',
            'Jasa Mahasiswa',
            'Alat Tulis',
            'Pakaian & Aksesoris',
            'Elektronik',
            'Makanan & Minuman',
        ]);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'icon' => fake()->randomElement(['book-open', 'beaker', 'device-phone', 'briefcase', 'pencil', 'shirt', 'cpu', 'cake']),
            'description' => 'Kategori ' . $name . ' untuk mahasiswa.',
            'sort_order' => fake()->numberBetween(0, 99),
        ];
    }
}
