<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public const PAYMENT_METHOD_COD = 'cod';

    public const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    public const PAYMENT_METHOD_EWALLET = 'e_wallet';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_COD => [
            'name' => 'Bayar Tunai di Kampus (COD)',
            'description' => 'Bayar tunai saat bertemu langsung di lingkungan kampus (Perpustakaan, Kantin, Gedung Kuliah). Gratis biaya admin.',
            'icon' => 'cash',
        ],
        self::PAYMENT_METHOD_BANK_TRANSFER => [
            'name' => 'Transfer Bank',
            'description' => 'Transfer rekening bank mahasiswa (BCA, Mandiri, BNI, BRI, BSI) via ATM atau Mobile Banking.',
            'icon' => 'bank',
        ],
        self::PAYMENT_METHOD_EWALLET => [
            'name' => 'Dompet Digital (E-Wallet)',
            'description' => 'Pembayaran praktis & instan menggunakan GoPay, OVO, DANA, atau ShopeePay.',
            'icon' => 'wallet',
        ],
    ];

    public const BANK_CHANNELS = [
        'bca' => 'Bank BCA',
        'mandiri' => 'Bank Mandiri',
        'bni' => 'Bank BNI',
        'bri' => 'Bank BRI',
        'bsi' => 'Bank Syariah Indonesia (BSI)',
    ];

    public const EWALLET_CHANNELS = [
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA',
        'shopeepay' => 'ShopeePay',
    ];

    public const PAYMENT_CHANNELS = [
        'cod' => 'Bayar Tunai di Kampus',
        'bca' => 'Bank BCA',
        'mandiri' => 'Bank Mandiri',
        'bni' => 'Bank BNI',
        'bri' => 'Bank BRI',
        'bsi' => 'Bank Syariah Indonesia (BSI)',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA',
        'shopeepay' => 'ShopeePay',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_STATUS_PENDING = 'pending';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_CONFIRMED = 'confirmed';

    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transaction_code',
        'product_id',
        'offer_id',
        'buyer_id',
        'seller_id',
        'amount',
        'payment_method',
        'payment_channel',
        'payment_status',
        'status',
        'meeting_location',
        'payment_proof',
        'payment_details',
        'buyer_notes',
        'seller_notes',
        'paid_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_details' => 'array',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = static::generateTransactionCode();
            }
        });
    }

    public static function generateTransactionCode(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return "TRX-{$date}-{$random}";
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method]['name'] ?? ucfirst($this->payment_method);
    }

    public function getPaymentChannelLabelAttribute(): string
    {
        return self::PAYMENT_CHANNELS[$this->payment_channel] ?? strtoupper($this->payment_channel ?? '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_COMPLETED => 'Transaksi Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'Belum Dibayar',
            self::PAYMENT_STATUS_PAID => 'Menunggu Konfirmasi Penjual',
            self::PAYMENT_STATUS_CONFIRMED => 'Pembayaran Terverifikasi',
            self::PAYMENT_STATUS_CANCELLED => 'Batal',
            default => ucfirst($this->payment_status),
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (! $this->payment_proof) {
            return null;
        }

        return Storage::disk('public')->url($this->payment_proof);
    }
}
