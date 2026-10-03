<?php

namespace App\Services\Verification;

use App\Models\Student;
use App\Models\University;

class DatabaseStudentVerificationProvider implements StudentVerificationProviderInterface
{
    /**
     * Verify student credentials against the internal database registry.
     */
    public function verify(
        University $university,
        string $studentNumber,
        string $email,
        string $name,
        ?string $major = null
    ): StudentVerificationResult {
        $studentNumber = trim($studentNumber);
        $email = strtolower(trim($email));
        $name = trim($name);

        // 1. Verify university email domain matches university record
        if (! $university->matchesEmailDomain($email)) {
            return StudentVerificationResult::failed(
                "Domain email tidak sesuai dengan domain resmi {$university->name} (@{$university->email_domain}).",
                ['university_email' => ["Domain email harus menggunakan @{$university->email_domain}."]]
            );
        }

        // 2. Check if student number exists for this university
        $student = Student::where('university_id', $university->id)
            ->where('student_number', $studentNumber)
            ->first();

        if (! $student) {
            // Check if student belongs to another university
            $belongsToOther = Student::where('student_number', $studentNumber)->exists();
            if ($belongsToOther) {
                return StudentVerificationResult::failed(
                    'Nomor Induk Mahasiswa (NIM) terdaftar pada universitas yang berbeda.',
                    ['student_number' => ['NIM ini terdaftar pada universitas yang berbeda. Silakan pilih universitas yang sesuai.']]
                );
            }

            return StudentVerificationResult::failed(
                'Data mahasiswa dengan NIM tersebut tidak ditemukan pada pangkalan data universitas.',
                ['student_number' => ['NIM tidak ditemukan pada data kemahasiswaan universitas ini.']]
            );
        }

        // 3. Verify student status is active
        if (! $student->is_active) {
            return StudentVerificationResult::failed(
                'Status kemahasiswaan Anda saat ini tidak aktif.',
                ['student_number' => ['Status kemahasiswaan tidak aktif. Hubungi bagian akademik kampus Anda.']]
            );
        }

        // 4. Verify registered student email matches registry
        if (strtolower(trim($student->university_email)) !== $email) {
            return StudentVerificationResult::failed(
                'Email universitas tidak cocok dengan email resmi yang terdaftar untuk NIM ini.',
                ['university_email' => ['Email universitas tidak cocok dengan data resmi pada NIM yang dimasukkan.']]
            );
        }

        // 5. Verify student name consistency
        if (! $this->isNameConsistent($name, $student->full_name)) {
            return StudentVerificationResult::failed(
                'Nama lengkap tidak sesuai dengan data kemahasiswaan yang terdaftar.',
                ['name' => ['Nama lengkap tidak sesuai dengan nama terdaftar di pangkalan data kampus.']]
            );
        }

        // 6. Check if student already has a registered user account
        if ($student->isRegistered()) {
            return StudentVerificationResult::failed(
                'Akun mahasiswa dengan NIM ini sudah terdaftar di CampusMarket.',
                ['student_number' => ['Akun mahasiswa dengan NIM ini sudah terdaftar. Silakan login.']]
            );
        }

        return StudentVerificationResult::success(
            student: $student,
            message: 'Status kemahasiswaan berhasil diverifikasi.'
        );
    }

    /**
     * Check name consistency allowing slight formatting / case differences.
     */
    protected function isNameConsistent(string $inputName, string $registryName): bool
    {
        $cleanInput = preg_replace('/\s+/', ' ', strtolower(trim($inputName)));
        $cleanRegistry = preg_replace('/\s+/', ' ', strtolower(trim($registryName)));

        if ($cleanInput === $cleanRegistry) {
            return true;
        }

        // Levenshtein / similar_text for minor typo tolerance if names are long enough
        similar_text($cleanInput, $cleanRegistry, $percent);
        return $percent >= 85.0;
    }
}
