<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the marketplace product categories (SRS-009).
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Buku per Semester', 'icon' => 'book-open', 'description' => 'Buku teks dan referensi yang sering dipakai tiap semester.', 'sort_order' => 1],
            ['name' => 'Perlengkapan Lab', 'icon' => 'beaker', 'description' => 'Alat laboratorium dan praktikum untuk kebutuhan kuliah.', 'sort_order' => 2],
            ['name' => 'Gadget Second', 'icon' => 'device-phone', 'description' => 'Laptop, ponsel, dan perangkat teknologi bekas layak pakai.', 'sort_order' => 3],
            ['name' => 'Jasa Mahasiswa', 'icon' => 'briefcase', 'description' => 'Les privat, pengetikan, desain, dan jasa lain antar mahasiswa.', 'sort_order' => 4],
            ['name' => 'Alat Tulis', 'icon' => 'pencil', 'description' => 'Peralatan tulis dan perlengkapan kuliah harian.', 'sort_order' => 5],
            ['name' => 'Pakaian & Aksesoris', 'icon' => 'shirt', 'description' => 'Pakaian, jaket lab, dan aksesoris mahasiswa.', 'sort_order' => 6],
            ['name' => 'Elektronik', 'icon' => 'cpu', 'description' => 'Perangkat elektronik dan komponen pendukung studi.', 'sort_order' => 7],
            ['name' => 'Makanan & Minuman', 'icon' => 'cake', 'description' => 'Snack, makanan, dan minuman khas kampus.', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($category['name'])],
                $category
            );
        }
    }
}
