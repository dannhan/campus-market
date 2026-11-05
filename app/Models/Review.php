<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    public const TYPE_SELLER_REVIEW = 'seller_review';

    public const TYPE_BUYER_REVIEW = 'buyer_review';

    public const SELLER_TAGS = [
        'fast_response' => 'Respon Cepat',
        'accurate_description' => 'Barang Sesuai Deskripsi',
        'good_packaging' => 'Packing Sangat Aman',
        'friendly' => 'Penjual Ramah',
        'on_time' => 'Tepat Waktu',
        'recommended' => 'Sangat Direkomendasikan',
    ];

    public const BUYER_TAGS = [
        'fast_payment' => 'Pembayaran Cepat',
        'smooth_communication' => 'Komunikasi Sangat Lancar',
        'polite' => 'Pembeli Sopan & Ramah',
        'punctual' => 'Tepat Waktu Saat Ketemu',
        'trusted' => 'Pembeli Terpercaya',
    ];

    protected $fillable = [
        'transaction_id',
        'reviewer_id',
        'reviewee_id',
        'product_id',
        'type',
        'rating',
        'comment',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'tags' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isSellerReview(): bool
    {
        return $this->type === self::TYPE_SELLER_REVIEW;
    }

    public function isBuyerReview(): bool
    {
        return $this->type === self::TYPE_BUYER_REVIEW;
    }

    public function getRatingStarsAttribute(): string
    {
        $rating = max(1, min(5, (int) $this->rating));

        return str_repeat('★', $rating).str_repeat('☆', 5 - $rating);
    }

    public function getFormattedRatingAttribute(): string
    {
        return number_format((float) $this->rating, 1).' ★';
    }
}
