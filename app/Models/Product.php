<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const CONDITIONS = ['new', 'like_new', 'used'];

    public const TRANSACTION_TYPES = ['sale', 'rent'];

    public const STATUSES = ['active', 'sold', 'withdrawn'];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'condition',
        'transaction_type',
        'status',
        'location',
        'bargainable',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'bargainable' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->title);
            }
        });
    }

    /**
     * Get the seller (student user) who listed this product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the uploaded images of this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary (cover) image of this product.
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the price offers made on this product.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get the wishlist records for this product.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the users who added this product to their wishlist.
     */
    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    /**
     * Determine whether the given user has saved this product.
     */
    public function isWishlistedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->wishlists()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the cover image URL with a graceful placeholder fallback.
     */
    public function getCoverImageUrlAttribute(): string
    {
        $primary = $this->images->firstWhere('is_primary') ?? $this->images->first();

        if ($primary && Storage::disk('public')->exists($primary->path)) {
            return Storage::disk('public')->url($primary->path);
        }

        return 'https://placehold.co/600x400/CCFBF1/0F766E?text=CampusMarket';
    }

    /**
     * Formatted price for display, e.g. "Rp 150.000".
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Human-readable condition label.
     */
    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'new' => 'Baru',
            'like_new' => 'Seperti Baru',
            'used' => 'Bekas',
            default => ucfirst($this->condition),
        };
    }

    /**
     * Human-readable transaction type label.
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return $this->transaction_type === 'rent' ? 'Disewakan' : 'Dijual';
    }

    /**
     * Only listings currently visible on the marketplace.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Free-text search across title and description.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Filter by seller's university (Campus Radius style browsing).
     */
    public function scopeForUniversity(Builder $query, ?int $universityId): Builder
    {
        if (! $universityId) {
            return $query;
        }

        return $query->whereHas('user.student', function (Builder $q) use ($universityId) {
            $q->where('university_id', $universityId);
        });
    }

    /**
     * Filter by price range in Rupiah.
     */
    public function scopePriceBetween(Builder $query, $min, $max): Builder
    {
        if ($min !== null && $min !== '') {
            $query->where('price', '>=', (float) $min);
        }

        if ($max !== null && $max !== '') {
            $query->where('price', '<=', (float) $max);
        }

        return $query;
    }

    /**
     * Generate a unique slug for the product title.
     */
    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'produk';
        $slug = $base;
        $attempt = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
            $attempt++;

            if ($attempt > 10) {
                $slug = $base.'-'.uniqid();
                break;
            }
        }

        return $slug;
    }
}
