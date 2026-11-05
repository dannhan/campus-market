<?php

namespace Database\Factories;

use App\Models\ItemRequest;
use App\Models\ItemRequestOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemRequestOffer>
 */
class ItemRequestOfferFactory extends Factory
{
    protected $model = ItemRequestOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_request_id' => ItemRequest::factory(),
            'user_id' => User::factory(),
            'product_id' => null,
            'price' => 50000,
            'message' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
