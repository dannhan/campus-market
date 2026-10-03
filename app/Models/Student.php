<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'student_number',
        'full_name',
        'major',
        'university_email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The university this student belongs to.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * The registered CampusMarket user account linked to this student registry entry.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Determine if this student record already has a registered user account.
     */
    public function isRegistered(): bool
    {
        return $this->user()->exists();
    }
}
