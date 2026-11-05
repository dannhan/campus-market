<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'product_id' => Product::factory(),
            'type' => Review::TYPE_SELLER_REVIEW,
            'rating' => fake()->numberBetween(4, 5),
            'comment' => fake()->sentence(8),
            'tags' => ['Respon Cepat', 'Barang Sesuai Deskripsi'],
        ];
    }

    public function sellerReview(): static
    {
        return $this->state(fn () => [
            'type' => Review::TYPE_SELLER_REVIEW,
            'tags' => ['Respon Cepat', 'Barang Sesuai Deskripsi'],
        ]);
    }

    public function buyerReview(): static
    {
        return $this->state(fn () => [
            'type' => Review::TYPE_BUYER_REVIEW,
            'tags' => ['Pembayaran Cepat', 'Komunikasi Sangat Lancar'],
        ]);
    }
}
