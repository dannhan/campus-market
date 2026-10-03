<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $university = University::factory();
        $nim = fake()->unique()->numerify('##########');
        $fullName = fake()->name();
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));

        return [
            'university_id' => $university,
            'student_number' => $nim,
            'full_name' => $fullName,
            'major' => fake()->randomElement([
                'Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Teknik Mesin',
                'Manajemen',
                'Akuntansi',
                'Ilmu Komunikasi',
                'Kedokteran',
            ]),
            'university_email' => function (array $attributes) use ($username) {
                $univ = University::find($attributes['university_id']);
                $domain = $univ ? $univ->email_domain : 'students.example.ac.id';
                return $username . '_' . fake()->numerify('###') . '@' . $domain;
            },
            'is_active' => true,
        ];
    }
}
