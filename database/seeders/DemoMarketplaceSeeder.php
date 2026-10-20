<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoProductSeeder;
use Database\Seeders\Demo\DemoUserSeeder;
use Illuminate\Database\Seeder;

class DemoMarketplaceSeeder extends Seeder
{
    /**
     * Seed the marketplace with demo students, sellers, and product listings.
     *
     * Demo accounts use the "demo." e-mail prefix and a reserved NIM range so
     * they can be identified and removed later without touching real data.
     */
    public function run(): void
    {
        $this->call([
            UniversitySeeder::class,
            CategorySeeder::class,
        ]);

        $this->call([
            DemoUserSeeder::class,
            DemoProductSeeder::class,
        ]);
    }
}
