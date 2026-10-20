<?php

namespace Database\Seeders\Demo;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Local part shared by every demo account e-mail.
     */
    public const EMAIL_PREFIX = 'demo.';

    /**
     * Password for every demo account.
     */
    public const PASSWORD = 'Password123!';

    /**
     * Demo students and their matching CampusMarket accounts.
     *
     * @var array<int, array{university: string, student_number: string, name: string, major: string, email: string}>
     */
    public const USERS = [
        [
            'university' => 'undip',
            'student_number' => '9900000001',
            'name' => 'Raka Pratama',
            'major' => 'Informatika',
            'email' => 'demo.raka@students.undip.ac.id',
        ],
        [
            'university' => 'undip',
            'student_number' => '9900000002',
            'name' => 'Maya Kusuma',
            'major' => 'Manajemen',
            'email' => 'demo.maya@students.undip.ac.id',
        ],
        [
            'university' => 'ugm',
            'student_number' => '9900000003',
            'name' => 'Bagas Saputra',
            'major' => 'Teknik Mesin',
            'email' => 'demo.bagas@mail.ugm.ac.id',
        ],
        [
            'university' => 'ugm',
            'student_number' => '9900000004',
            'name' => 'Tari Wulandari',
            'major' => 'Ilmu Komunikasi',
            'email' => 'demo.tari@mail.ugm.ac.id',
        ],
        [
            'university' => 'itb',
            'student_number' => '9900000005',
            'name' => 'Yoga Ramadhan',
            'major' => 'Teknik Informatika',
            'email' => 'demo.yoga@mahasiswa.itb.ac.id',
        ],
        [
            'university' => 'itb',
            'student_number' => '9900000006',
            'name' => 'Sinta Maharani',
            'major' => 'Desain Komunikasi Visual',
            'email' => 'demo.sinta@mahasiswa.itb.ac.id',
        ],
        [
            'university' => 'ui',
            'student_number' => '9900000007',
            'name' => 'Aldi Firmansyah',
            'major' => 'Ilmu Komputer',
            'email' => 'demo.aldi@ui.ac.id',
        ],
        [
            'university' => 'ui',
            'student_number' => '9900000008',
            'name' => 'Putri Anjani',
            'major' => 'Akuntansi',
            'email' => 'demo.putri@ui.ac.id',
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $data) {
            $university = University::where('slug', $data['university'])->firstOrFail();

            $student = Student::updateOrCreate(
                [
                    'university_id' => $university->id,
                    'student_number' => $data['student_number'],
                ],
                [
                    'full_name' => $data['name'],
                    'major' => $data['major'],
                    'university_email' => $data['email'],
                    'is_active' => true,
                ]
            );

            $user = User::firstOrNew(['email' => $data['email']]);
            $user->fill([
                'name' => $data['name'],
                'student_id' => $student->id,
                'email_verified_at' => now(),
            ]);

            if (! $user->exists) {
                $user->password = Hash::make(self::PASSWORD);
            }

            $user->save();
        }
    }
}
