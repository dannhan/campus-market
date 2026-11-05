<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EscrowPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING_DEPOSIT = 'pending_deposit';

    public const STATUS_HELD = 'held';

    public const STATUS_RELEASED = 'released';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_DISPUTED = 'disputed';

    public const STATUSES = [
        self::STATUS_PENDING_DEPOSIT,
        self::STATUS_HELD,
        self::STATUS_RELEASED,
        self::STATUS_REFUNDED,
        self::STATUS_DISPUTED,
    ];

    public const HOLDING_ACCOUNTS = [
        'bca' => [
            'type' => 'bank',
            'name' => 'Bank BCA',
            'account_number' => '8801-2345-67',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ],
        'mandiri' => [
            'type' => 'bank',
            'name' => 'Bank Mandiri',
            'account_number' => '137-00-9876543-2',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ],
        'bni' => [
            'type' => 'bank',
            'name' => 'Bank BNI',
            'account_number' => '0234-5678-90',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ],
        'bri' => [
            'type' => 'bank',
            'name' => 'Bank BRI',
            'account_number' => '0012-01-098765-50-1',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ],
        'bsi' => [
            'type' => 'bank',
            'name' => 'Bank Syariah Indonesia (BSI)',
            'account_number' => '7123-456-789',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ],
        'gopay' => [
            'type' => 'ewallet',
            'name' => 'GoPay Rekber',
            'account_number' => '0811-9988-7766',
            'account_name' => 'Rekber CampusMarket Mahasiswa',
        ],
        'ovo' => [
            'type' => 'ewallet',
            'name' => 'OVO Rekber',
            'account_number' => '0811-9988-7766',
            'account_name' => 'Rekber CampusMarket Mahasiswa',
        ],
        'dana' => [
            'type' => 'ewallet',
            'name' => 'DANA Rekber',
            'account_number' => '0811-9988-7766',
            'account_name' => 'Rekber CampusMarket Mahasiswa',
        ],
        'shopeepay' => [
            'type' => 'ewallet',
            'name' => 'ShopeePay Rekber',
            'account_number' => '0811-9988-7766',
            'account_name' => 'Rekber CampusMarket Mahasiswa',
        ],
    ];

    protected $fillable = [
        'transaction_id',
        'escrow_code',
        'amount',
        'shipping_cost',
        'platform_fee',
        'total_amount',
        'status',
        'holding_channel',
        'holding_account_number',
        'holding_account_name',
        'deposit_proof',
        'deposited_at',
        'verified_at',
        'verified_by_id',
        'released_at',
        'refunded_at',
        'seller_payout_details',
        'buyer_refund_details',
        'release_notes',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'seller_payout_details' => 'array',
        'buyer_refund_details' => 'array',
        'deposited_at' => 'datetime',
        'verified_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EscrowPayment $escrow) {
            if (empty($escrow->escrow_code)) {
                $escrow->escrow_code = self::generateEscrowCode();
            }
        });
    }

    public static function generateEscrowCode(): string
    {
        do {
            $code = 'ESC-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (self::where('escrow_code', $code)->exists());

        return $code;
    }

    public static function getHoldingAccount(string $channel): array
    {
        return self::HOLDING_ACCOUNTS[$channel] ?? [
            'type' => 'bank',
            'name' => 'Rekber CampusMarket',
            'account_number' => '8801-2345-67',
            'account_name' => 'Rekber CampusMarket Indonesia',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_DEPOSIT => 'Menunggu Pembeli Transfer ke Rekber',
            self::STATUS_HELD => 'Dana Diamankan di Rekber Kampus',
            self::STATUS_RELEASED => 'Dana Diteruskan ke Penjual',
            self::STATUS_REFUNDED => 'Dana Dikembalikan ke Pembeli',
            self::STATUS_DISPUTED => 'Dana Dibekukan (Dalam Mediasi Sengketa)',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_DEPOSIT => 'bg-amber-100 text-amber-800 border-amber-200',
            self::STATUS_HELD => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_RELEASED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::STATUS_REFUNDED => 'bg-purple-100 text-purple-800 border-purple-200',
            self::STATUS_DISPUTED => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_amount, 0, ',', '.');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    public function getDepositProofUrlAttribute(): ?string
    {
        if (! $this->deposit_proof) {
            return null;
        }

        return Storage::disk('public')->url($this->deposit_proof);
    }

    /**
     * Mark escrow as held (funds verified in official holding account).
     */
    public function markHeld(?User $verifier = null): void
    {
        $this->update([
            'status' => self::STATUS_HELD,
            'verified_at' => now(),
            'verified_by_id' => $verifier?->id,
        ]);

        $this->transaction->update([
            'payment_status' => Transaction::PAYMENT_STATUS_PAID,
            'status' => Transaction::STATUS_PROCESSING,
            'paid_at' => now(),
        ]);
    }

    /**
     * Release escrow funds to seller after buyer receives the goods.
     */
    public function releaseToSeller(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RELEASED,
            'released_at' => now(),
            'release_notes' => $notes,
        ]);

        $this->transaction->update([
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_COMPLETED,
            'confirmed_at' => now(),
            'delivered_at' => $this->transaction->delivered_at ?? now(),
        ]);
    }

    /**
     * Refund escrow funds to buyer in case of cancellation or dispute resolution.
     */
    public function refundToBuyer(string $reason, ?array $refundDetails = null): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'buyer_refund_details' => $refundDetails ?? $this->buyer_refund_details,
        ]);

        $this->transaction->update([
            'status' => Transaction::STATUS_CANCELLED,
            'payment_status' => Transaction::PAYMENT_STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->transaction->product->update(['status' => 'active']);
    }

    /**
     * Freeze escrow funds due to dispute.
     */
    public function markDisputed(): void
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
        ]);
    }
}
