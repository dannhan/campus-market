<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<University>
 */
class UniversityFactory extends Factory
{
    protected $model = University::class;

    public function definition(): array
    {
        $name = fake()->unique()->company() . ' University';
        $domain = Str::slug($name) . '.ac.id';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'email_domain' => $domain,
        ];
    }
}
