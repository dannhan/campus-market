<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    public const STATUSES = ['pending', 'accepted', 'rejected', 'countered', 'withdrawn'];

    protected $fillable = [
        'product_id',
        'user_id',
        'parent_offer_id',
        'offered_price',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'offered_price' => 'decimal:2',
        ];
    }

    /**
     * The product being negotiated.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The user who proposed this price (buyer for root offers, either party for counters).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The offer this one counters, if any.
     */
    public function parentOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'parent_offer_id');
    }

    /**
     * Counter-offers spawned from this offer.
     */
    public function counterOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'parent_offer_id');
    }

    /**
     * Formatted offer amount for display.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->offered_price, 0, ',', '.');
    }

    /**
     * Whether this offer is still actionable by the counterparties.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
