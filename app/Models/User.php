<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'student_id',
        'email_verified_at',
        'avatar',
        'seller_rating',
        'buyer_rating',
        'seller_review_count',
        'buyer_review_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'seller_rating' => 'decimal:2',
            'buyer_rating' => 'decimal:2',
            'seller_review_count' => 'integer',
            'buyer_review_count' => 'integer',
        ];
    }

    /**
     * The verified university student registry entry.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function hasWishlisted(Product $product): bool
    {
        return $this->wishlists()->where('product_id', $product->id)->exists();
    }

    /**
     * Convenience accessor for the user's university.
     */
    public function getUniversityAttribute(): ?University
    {
        return $this->student?->university;
    }

    /**
     * Check if user is linked to a valid student registry entry.
     */
    public function isStudent(): bool
    {
        return $this->student_id !== null && $this->student !== null;
    }

    /**
     * Check if user is a verified student with verified email.
     */
    public function isVerifiedStudent(): bool
    {
        return $this->isStudent() && $this->hasVerifiedEmail();
    }

    /**
     * Avatar URL or fallback default initials avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        $initials = urlencode($this->name ?: 'Mahasiswa');

        return "https://ui-avatars.com/api/?name={$initials}&color=0D9488&background=CCFBF1";
    }

    /**
     * Formatted seller rating for display.
     */
    public function getSellerRatingDisplayAttribute(): string
    {
        if ($this->seller_rating === null || $this->seller_review_count === 0) {
            return 'Belum ada rating';
        }

        return number_format((float) $this->seller_rating, 1).' / 5.0 ('.$this->seller_review_count.' ulasan)';
    }

    /**
     * Formatted buyer rating for display.
     */
    public function getBuyerRatingDisplayAttribute(): string
    {
        if ($this->buyer_rating === null || $this->buyer_review_count === 0) {
            return 'Belum ada rating';
        }

        return number_format((float) $this->buyer_rating, 1).' / 5.0 ('.$this->buyer_review_count.' ulasan)';
    }
}
