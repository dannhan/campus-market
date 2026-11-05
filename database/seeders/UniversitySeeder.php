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
                'city' => 'Kota Semarang',
                'province' => 'Jawa Tengah',
                'latitude' => -7.0504000,
                'longitude' => 110.4393000,
                'address' => 'Jl. Prof. Soedarto, SH, Tembalang, Kota Semarang',
            ],
            [
                'name' => 'Universitas Negeri Semarang',
                'slug' => 'unnes',
                'email_domain' => 'mail.unnes.ac.id',
                'city' => 'Kota Semarang',
                'province' => 'Jawa Tengah',
                'latitude' => -7.0494000,
                'longitude' => 110.4076000,
                'address' => 'Sekaran, Gunungpati, Kota Semarang',
            ],
            [
                'name' => 'Universitas Dian Nuswantoro',
                'slug' => 'udinus',
                'email_domain' => 'mhs.dinus.ac.id',
                'city' => 'Kota Semarang',
                'province' => 'Jawa Tengah',
                'latitude' => -6.9826000,
                'longitude' => 110.4091000,
                'address' => 'Jl. Imam Bonjol No. 207, Pendrikan Kidul, Kota Semarang',
            ],
            [
                'name' => 'Universitas Gadjah Mada',
                'slug' => 'ugm',
                'email_domain' => 'mail.ugm.ac.id',
                'city' => 'Kab. Sleman',
                'province' => 'D.I. Yogyakarta',
                'latitude' => -7.7713000,
                'longitude' => 110.3775000,
                'address' => 'Bulaksumur, Caturtunggal, Kab. Sleman, D.I. Yogyakarta',
            ],
            [
                'name' => 'Institut Teknologi Bandung',
                'slug' => 'itb',
                'email_domain' => 'mahasiswa.itb.ac.id',
                'city' => 'Kota Bandung',
                'province' => 'Jawa Barat',
                'latitude' => -6.8915000,
                'longitude' => 107.6107000,
                'address' => 'Jl. Ganesa No. 10, Coblong, Kota Bandung',
            ],
            [
                'name' => 'Universitas Indonesia',
                'slug' => 'ui',
                'email_domain' => 'ui.ac.id',
                'city' => 'Kota Depok',
                'province' => 'Jawa Barat',
                'latitude' => -6.3653000,
                'longitude' => 106.8286000,
                'address' => 'Pondok Cina, Beji, Kota Depok',
            ],
            [
                'name' => 'Universitas Airlangga',
                'slug' => 'unair',
                'email_domain' => 'mhs.unair.ac.id',
                'city' => 'Kota Surabaya',
                'province' => 'Jawa Timur',
                'latitude' => -7.2704000,
                'longitude' => 112.7588000,
                'address' => 'Jl. Airlangga No. 4 - 6, Gubeng, Kota Surabaya',
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
