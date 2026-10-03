<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'student_id' => null,
            'avatar' => null,
            'seller_rating' => null,
            'buyer_rating' => null,
            'seller_review_count' => 0,
            'buyer_review_count' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is linked to a student.
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn () => [
            'name' => $student->full_name,
            'email' => $student->university_email,
            'student_id' => $student->id,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
