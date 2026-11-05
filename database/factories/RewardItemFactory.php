<?php

namespace Database\Factories;

use App\Models\RewardItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RewardItem>
 */
class RewardItemFactory extends Factory
{
    protected $model = RewardItem::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Voucher Diskon Belanja Rp 10.000',
            'Voucher Bebas Ongkos Kirim Kampus',
            'Tote Bag Eksklusif CampusMarket',
            'Badge Mahasiswa Teladan',
            'Voucher Potongan Fotokopi & Buku Rp 15.000',
        ]).' #'.fake()->unique()->numberBetween(100, 9999);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(8),
            'points_cost' => fake()->randomElement([30, 50, 75, 100, 200]),
            'reward_type' => fake()->randomElement([
                RewardItem::TYPE_DISCOUNT_FIXED,
                RewardItem::TYPE_FREE_SHIPPING,
                RewardItem::TYPE_MERCHANDISE,
            ]),
            'discount_amount' => 10000,
            'stock' => fake()->randomElement([-1, 10, 25, 50]),
            'is_active' => true,
            'validity_days' => 30,
            'icon' => 'ticket',
        ];
    }
}
