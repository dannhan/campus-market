<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardRedemption extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_USED = 'used';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_FULFILLED = 'fulfilled';

    protected $fillable = [
        'user_id',
        'reward_item_id',
        'points_spent',
        'voucher_code',
        'status',
        'expires_at',
        'used_at',
        'admin_notes',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(RewardItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif & Siap Digunakan',
            self::STATUS_USED => 'Telah Digunakan',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_FULFILLED => 'Terpenuhi / Diberikan',
            default => ucfirst($this->status),
        };
    }
}
