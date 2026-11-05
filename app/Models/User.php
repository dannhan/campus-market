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
        'is_admin',
        'student_id',
        'student_status',
        'email_verified_at',
        'avatar',
        'seller_rating',
        'buyer_rating',
        'seller_review_count',
        'buyer_review_count',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'ewallet_provider',
        'ewallet_account_number',
        'ewallet_account_name',
        'preferred_meeting_location',
        'preferred_pickup_location',
    ];

    protected $attributes = [
        'is_admin' => false,
        'student_status' => 'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
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

    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reportedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'reporter_id');
    }

    public function receivedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'respondent_id');
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
     * Get total unread messages across all active conversations.
     */
    public function unreadMessagesCount(): int
    {
        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->whereHas('conversation', function ($q) {
                $q->where('buyer_id', $this->id)->orWhere('seller_id', $this->id);
            })
            ->count();
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
     * Check if user has administrator privileges.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Check if user is a verified student with verified email.
     */
    public function isVerifiedStudent(): bool
    {
        return $this->isStudent() && $this->hasVerifiedEmail();
    }

    /**
     * Check if user is an alumnus.
     */
    public function isAlumni(): bool
    {
        return $this->student_status === 'alumni';
    }

    /**
     * Check if user is an active student.
     */
    public function isActiveStudent(): bool
    {
        return $this->student_status === 'active' || $this->student_status === null;
    }

    /**
     * Human-readable label for the student status.
     */
    public function getStudentStatusLabelAttribute(): string
    {
        return match ($this->student_status) {
            'alumni' => 'Alumni',
            default => 'Mahasiswa Aktif',
        };
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

    public function purchases(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function sellerReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id')->where('type', Review::TYPE_SELLER_REVIEW);
    }

    public function buyerReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id')->where('type', Review::TYPE_BUYER_REVIEW);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Recalculate and update the aggregated seller and buyer ratings.
     */
    public function recalculateRatings(): void
    {
        $sellerStats = $this->reviewsReceived()
            ->where('type', Review::TYPE_SELLER_REVIEW)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating')
            ->first();

        $this->seller_review_count = (int) ($sellerStats->total ?? 0);
        $this->seller_rating = $this->seller_review_count > 0 ? round((float) $sellerStats->avg_rating, 2) : null;

        $buyerStats = $this->reviewsReceived()
            ->where('type', Review::TYPE_BUYER_REVIEW)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating')
            ->first();

        $this->buyer_review_count = (int) ($buyerStats->total ?? 0);
        $this->buyer_rating = $this->buyer_review_count > 0 ? round((float) $buyerStats->avg_rating, 2) : null;

        $this->saveQuietly();
    }

    public function hasPaymentSettings(): bool
    {
        return ! empty($this->bank_account_number) || ! empty($this->ewallet_account_number) || ! empty($this->preferred_meeting_location);
    }

    public function getBankNameLabelAttribute(): ?string
    {
        return Transaction::BANK_CHANNELS[$this->bank_name] ?? strtoupper($this->bank_name ?? '');
    }

    public function getEwalletProviderLabelAttribute(): ?string
    {
        return Transaction::EWALLET_CHANNELS[$this->ewallet_provider] ?? ucfirst($this->ewallet_provider ?? '');
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
