<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    use HasFactory;

    public const TYPE_DAILY_CHECK_IN = 'daily_check_in';

    public const TYPE_TRANSACTION_COMPLETED = 'transaction_completed';

    public const TYPE_REVIEW_SUBMITTED = 'review_submitted';

    public const TYPE_PRODUCT_LISTED = 'product_listed';

    public const TYPE_WANTED_OFFER_ACCEPTED = 'wanted_offer_accepted';

    public const TYPE_REWARD_REDEMPTION = 'reward_redemption';

    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'reference_type',
        'reference_id',
        'created_by_admin_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'reference_id' => 'integer',
        'created_by_admin_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_DAILY_CHECK_IN => 'Daily Check-in',
            self::TYPE_TRANSACTION_COMPLETED => 'Transaksi Selesai',
            self::TYPE_REVIEW_SUBMITTED => 'Ulasan & Rating',
            self::TYPE_PRODUCT_LISTED => 'Pasang Produk',
            self::TYPE_WANTED_OFFER_ACCEPTED => 'Bantuan Cari Barang',
            self::TYPE_REWARD_REDEMPTION => 'Tukar Reward',
            self::TYPE_ADMIN_ADJUSTMENT => 'Penyesuaian Admin',
            default => 'Aktivitas Mahasiswa',
        };
    }
}
