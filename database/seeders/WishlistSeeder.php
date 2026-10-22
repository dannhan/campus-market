<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budi = User::where('email', 'budi.santoso@students.undip.ac.id')->first();
        $fajar = User::where('email', 'fajar.nugroho@mail.ugm.ac.id')->first();

        // 1. Fajar wishlists Budi's product
        $budiProduct = Product::where('user_id', $budi?->id)->first();
        if ($fajar && $budiProduct) {
            Wishlist::updateOrCreate([
                'user_id' => $fajar->id,
                'product_id' => $budiProduct->id,
            ]);
        }

        // 2. Budi wishlists another student's product
        $otherProduct = Product::where('user_id', '!=', $budi?->id)->first();
        if ($budi && $otherProduct) {
            Wishlist::updateOrCreate([
                'user_id' => $budi->id,
                'product_id' => $otherProduct->id,
            ]);
        }
    }
}
