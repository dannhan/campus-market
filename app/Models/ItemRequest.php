<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ItemRequest extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_CLOSED = 'closed';

    public const URGENCY_URGENT = 'urgent';

    public const URGENCY_NORMAL = 'normal';

    public const URGENCY_LOW = 'low';

    public const CONDITION_ANY = 'any';

    public const CONDITION_NEW = 'new';

    public const CONDITION_LIKE_NEW = 'like_new';

    public const CONDITION_USED = 'used';

    protected $fillable = [
        'user_id',
        'category_id',
        'university_id',
        'title',
        'slug',
        'description',
        'budget_min',
        'budget_max',
        'condition_preference',
        'urgency',
        'status',
        'views_count',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'views_count' => 'integer',
            'fulfilled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ItemRequest $request) {
            if (empty($request->slug)) {
                $request->slug = static::generateUniqueSlug($request->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'permintaan';
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

    /**
     * Get the student who posted this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product category for this item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the campus/university where this item is needed.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Offers or responses submitted by other students.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(ItemRequestOffer::class)->latest();
    }

    /**
     * The accepted offer when fulfilled.
     */
    public function acceptedOffer(): HasOne
    {
        return $this->hasOne(ItemRequestOffer::class)->where('status', 'accepted');
    }

    /**
     * Formatted budget range display string.
     */
    public function getFormattedBudgetAttribute(): string
    {
        $min = (float) $this->budget_min;
        $max = (float) $this->budget_max;

        if ($min > 0 && $max > 0) {
            return 'Rp '.number_format($min, 0, ',', '.').' - Rp '.number_format($max, 0, ',', '.');
        } elseif ($max > 0) {
            return 'Maks. Rp '.number_format($max, 0, ',', '.');
        } elseif ($min > 0) {
            return 'Mulai Rp '.number_format($min, 0, ',', '.');
        }

        return 'Budget Bebas / Sesuai Kondisi';
    }

    /**
     * Human-readable label for condition preference.
     */
    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition_preference) {
            self::CONDITION_NEW => 'Hanya Barang Baru',
            self::CONDITION_LIKE_NEW => 'Seperti Baru (Mulus)',
            self::CONDITION_USED => 'Bekas / Masih Layak',
            default => 'Kondisi Apapun (Bebas)',
        };
    }

    /**
     * Human-readable urgency badge label.
     */
    public function getUrgencyLabelAttribute(): string
    {
        return match ($this->urgency) {
            self::URGENCY_URGENT => 'Mendesak / Butuh Cepat',
            self::URGENCY_LOW => 'Santai',
            default => 'Normal',
        };
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_FULFILLED => 'Sudah Ditemukan',
            self::STATUS_CLOSED => 'Permintaan Ditutup',
            default => 'Sedang Dicari',
        };
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }
}
