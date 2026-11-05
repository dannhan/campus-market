<?php

namespace Database\Factories;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointTransaction>
 */
class PointTransactionFactory extends Factory
{
    protected $model = PointTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomElement([10, 15, 25, 40, 50]),
            'type' => PointTransaction::TYPE_DAILY_CHECK_IN,
            'description' => 'Aktivitas Mahasiswa Aktif',
            'reference_type' => null,
            'reference_id' => null,
            'created_by_admin_id' => null,
        ];
    }
}
