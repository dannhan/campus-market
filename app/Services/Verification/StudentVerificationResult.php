<?php

namespace App\Services\Verification;

use App\Models\Student;

class StudentVerificationResult
{
    public function __construct(
        protected bool $success,
        protected string $message,
        protected array $errors = [],
        protected ?Student $student = null
    ) {}

    public static function success(Student $student, string $message = 'Verifikasi mahasiswa berhasil.'): self
    {
        return new self(
            success: true,
            message: $message,
            errors: [],
            student: $student
        );
    }

    public static function failed(string $message, array $errors = []): self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            student: null
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return ! $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return $this->message;
        }

        $first = reset($this->errors);
        return is_array($first) ? ($first[0] ?? $this->message) : $first;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }
}
