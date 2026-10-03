<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            [
                'name' => 'Universitas Diponegoro',
                'slug' => 'undip',
                'email_domain' => 'students.undip.ac.id',
            ],
            [
                'name' => 'Universitas Gadjah Mada',
                'slug' => 'ugm',
                'email_domain' => 'mail.ugm.ac.id',
            ],
            [
                'name' => 'Institut Teknologi Bandung',
                'slug' => 'itb',
                'email_domain' => 'mahasiswa.itb.ac.id',
            ],
            [
                'name' => 'Universitas Indonesia',
                'slug' => 'ui',
                'email_domain' => 'ui.ac.id',
            ],
        ];

        foreach ($universities as $univ) {
            University::updateOrCreate(
                ['slug' => $univ['slug']],
                $univ
            );
        }
    }
}
