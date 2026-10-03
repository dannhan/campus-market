<?php

namespace App\Services\Verification;

use App\Models\University;

interface StudentVerificationProviderInterface
{
    /**
     * Verify student credentials against the academic database or institution provider.
     */
    public function verify(
        University $university,
        string $studentNumber,
        string $email,
        string $name,
        ?string $major = null
    ): StudentVerificationResult;
}
