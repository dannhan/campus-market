<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\University;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $undip = University::where('slug', 'undip')->firstOrFail();
        $ugm = University::where('slug', 'ugm')->firstOrFail();
        $itb = University::where('slug', 'itb')->firstOrFail();
        $ui = University::where('slug', 'ui')->firstOrFail();

        $students = [
            // UNDIP Students
            [
                'university_id' => $undip->id,
                'student_number' => '24060122130001',
                'full_name' => 'Budi Santoso',
                'major' => 'Informatika',
                'university_email' => 'budi.santoso@students.undip.ac.id',
                'is_active' => true,
            ],
            [
                'university_id' => $undip->id,
                'student_number' => '24060122130002',
                'full_name' => 'Siti Rahmawati',
                'major' => 'Sistem Informasi',
                'university_email' => 'siti.rahmawati@students.undip.ac.id',
                'is_active' => true,
            ],
            [
                'university_id' => $undip->id,
                'student_number' => '24060122130003',
                'full_name' => 'Ahmad Pratama',
                'major' => 'Teknik Elektro',
                'university_email' => 'ahmad.pratama@students.undip.ac.id',
                'is_active' => true,
            ],
            [
                'university_id' => $undip->id,
                'student_number' => '24060122130004',
                'full_name' => 'Rina Melati',
                'major' => 'Manajemen',
                'university_email' => 'rina.melati@students.undip.ac.id',
                'is_active' => true,
            ],

            // UGM Students
            [
                'university_id' => $ugm->id,
                'student_number' => '22/498765/TK/54321',
                'full_name' => 'Dewi Lestari',
                'major' => 'Teknik Industri',
                'university_email' => 'dewi.lestari@mail.ugm.ac.id',
                'is_active' => true,
            ],
            [
                'university_id' => $ugm->id,
                'student_number' => '22/498766/SV/54322',
                'full_name' => 'Fajar Nugroho',
                'major' => 'Teknologi Rekayasa Perangkat Lunak',
                'university_email' => 'fajar.nugroho@mail.ugm.ac.id',
                'is_active' => true,
            ],

            // ITB Students
            [
                'university_id' => $itb->id,
                'student_number' => '13522001',
                'full_name' => 'Reza Firmansyah',
                'major' => 'Teknik Informatika',
                'university_email' => 'reza.firmansyah@mahasiswa.itb.ac.id',
                'is_active' => true,
            ],

            // UI Students
            [
                'university_id' => $ui->id,
                'student_number' => '2206812345',
                'full_name' => 'Nadia Putri',
                'major' => 'Ilmu Komputer',
                'university_email' => 'nadia.putri@ui.ac.id',
                'is_active' => true,
            ],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(
                [
                    'university_id' => $student['university_id'],
                    'student_number' => $student['student_number'],
                ],
                $student
            );
        }
    }
}
