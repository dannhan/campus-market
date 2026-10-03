<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email_domain',
    ];

    /**
     * Get the student registry entries for this university.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get registered users belonging to this university through students.
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Student::class);
    }

    /**
     * Check if a given email matches this university's domain.
     */
    public function matchesEmailDomain(string $email): bool
    {
        $email = strtolower(trim($email));
        $domain = strtolower(trim($this->email_domain));

        if (! str_contains($email, '@')) {
            return false;
        }

        $emailParts = explode('@', $email);
        $emailDomain = end($emailParts);

        return $emailDomain === $domain;
    }
}
