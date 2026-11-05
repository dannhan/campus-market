<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ItemRequest;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemRequest>
 */
class ItemRequestFactory extends Factory
{
    protected $model = ItemRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'university_id' => University::factory(),
            'title' => ucfirst($title),
            'slug' => ItemRequest::generateUniqueSlug($title),
            'description' => $this->faker->paragraph(),
            'budget_min' => 20000,
            'budget_max' => 100000,
            'condition_preference' => 'any',
            'urgency' => 'normal',
            'status' => 'open',
            'views_count' => 0,
        ];
    }
}
