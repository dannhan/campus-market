<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReport>
 */
class ProductReportFactory extends Factory
{
    protected $model = ProductReport::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'reporter_id' => User::factory(),
            'reason' => fake()->randomElement(array_keys(ProductReport::REASONS)),
            'details' => fake()->sentence(8),
            'status' => ProductReport::STATUS_PENDING,
            'reviewed_by_id' => null,
            'reviewed_at' => null,
        ];
    }
}
