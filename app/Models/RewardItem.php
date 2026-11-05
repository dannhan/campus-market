<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RewardItem extends Model
{
    use HasFactory;

    public const TYPE_DISCOUNT_FIXED = 'discount_fixed';

    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const TYPE_MERCHANDISE = 'merchandise';

    public const TYPE_BADGE = 'badge';

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'points_cost',
        'reward_type',
        'discount_amount',
        'stock',
        'is_active',
        'validity_days',
        'icon',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'discount_amount' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'validity_days' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RewardItem $item) {
            if (empty($item->slug)) {
                $baseSlug = Str::slug($item->title);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $item->slug = $slug;
            }
        });
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function isUnlimitedStock(): bool
    {
        return $this->stock === -1;
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ($this->isUnlimitedStock() || $this->stock > 0);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->reward_type) {
            self::TYPE_DISCOUNT_FIXED => 'Diskon Belanja',
            self::TYPE_FREE_SHIPPING => 'Gratis Ongkir',
            self::TYPE_MERCHANDISE => 'Merchandise Kampus',
            self::TYPE_BADGE => 'Badge Profil Khusus',
            default => 'Reward Spesial',
        };
    }

    public function getFormattedDiscountAttribute(): string
    {
        if ($this->reward_type === self::TYPE_FREE_SHIPPING) {
            return 'Potongan Ongkir';
        }

        if ($this->discount_amount > 0) {
            return 'Rp '.number_format($this->discount_amount, 0, ',', '.');
        }

        return '-';
    }
}
