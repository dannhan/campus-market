<?php

namespace App\Services\Verification;

use App\Models\University;

class StudentVerificationService
{
    public function __construct(
        protected StudentVerificationProviderInterface $provider
    ) {}

    /**
     * Verify student data against academic records.
     */
    public function verify(
        University $university,
        string $studentNumber,
        string $email,
        string $name,
        ?string $major = null
    ): StudentVerificationResult {
        return $this->provider->verify(
            university: $university,
            studentNumber: $studentNumber,
            email: $email,
            name: $name,
            major: $major
        );
    }

    /**
     * Verify student data by university ID.
     */
    public function verifyByUniversityId(
        int $universityId,
        string $studentNumber,
        string $email,
        string $name,
        ?string $major = null
    ): StudentVerificationResult {
        $university = University::find($universityId);

        if (! $university) {
            return StudentVerificationResult::failed(
                'Universitas yang dipilih tidak valid atau belum terdaftar di CampusMarket.',
                ['university_id' => ['Universitas yang dipilih tidak valid.']]
            );
        }

        return $this->verify(
            university: $university,
            studentNumber: $studentNumber,
            email: $email,
            name: $name,
            major: $major
        );
    }

    /**
     * Check whether an email matches a university's allowed domain.
     */
    public function validateDomain(University $university, string $email): bool
    {
        return $university->matchesEmailDomain($email);
    }
}
