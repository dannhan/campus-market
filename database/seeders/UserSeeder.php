<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Budi Santoso (UNDIP) - Verified User
        $budiStudent = Student::where('student_number', '24060122130001')->first();
        if ($budiStudent) {
            User::updateOrCreate(
                ['email' => $budiStudent->university_email],
                [
                    'name' => $budiStudent->full_name,
                    'password' => Hash::make('Password123!'),
                    'student_id' => $budiStudent->id,
                    'email_verified_at' => now(),
                    'seller_rating' => null,
                    'buyer_rating' => null,
                    'seller_review_count' => 0,
                    'buyer_review_count' => 0,
                ]
            );
        }

        // 2. Siti Rahmawati (UNDIP) - Unverified User (for email verification flow testing)
        $sitiStudent = Student::where('student_number', '24060122130002')->first();
        if ($sitiStudent) {
            User::updateOrCreate(
                ['email' => $sitiStudent->university_email],
                [
                    'name' => $sitiStudent->full_name,
                    'password' => Hash::make('Password123!'),
                    'student_id' => $sitiStudent->id,
                    'email_verified_at' => null,
                    'seller_rating' => null,
                    'buyer_rating' => null,
                    'seller_review_count' => 0,
                    'buyer_review_count' => 0,
                ]
            );
        }

        // 3. Fajar Nugroho (UGM) - Verified User
        $fajarStudent = Student::where('student_number', '22/498766/SV/54322')->first();
        if ($fajarStudent) {
            User::updateOrCreate(
                ['email' => $fajarStudent->university_email],
                [
                    'name' => $fajarStudent->full_name,
                    'password' => Hash::make('Password123!'),
                    'student_id' => $fajarStudent->id,
                    'email_verified_at' => now(),
                    'seller_rating' => null,
                    'buyer_rating' => null,
                    'seller_review_count' => 0,
                    'buyer_review_count' => 0,
                ]
            );
        }
    }
}
