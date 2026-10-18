<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'parent_offer_id' => null,
            'offered_price' => fake()->numberBetween(5, 60) * 1000,
            'message' => fake()->boolean() ? fake()->sentence() : null,
            'status' => 'pending',
        ];
    }

    /**
     * Attach the offer to a specific product and offerer.
     */
    public function forProduct(Product $product, User $user): static
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Make this offer a counter of another offer.
     */
    public function countering(Offer $parent, User $counterer): static
    {
        return $this->state(fn () => [
            'product_id' => $parent->product_id,
            'user_id' => $counterer->id,
            'parent_offer_id' => $parent->id,
        ]);
    }
}
