<?php

namespace Database\Factories;

use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RewardRedemption>
 */
class RewardRedemptionFactory extends Factory
{
    protected $model = RewardRedemption::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reward_item_id' => RewardItem::factory(),
            'points_spent' => 50,
            'voucher_code' => 'CMP-'.strtoupper(Str::random(4)).'-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => RewardRedemption::STATUS_ACTIVE,
            'expires_at' => now()->addDays(30),
            'used_at' => null,
            'admin_notes' => null,
        ];
    }
}
