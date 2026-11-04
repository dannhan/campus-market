<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const REASON_NOT_AS_DESCRIBED = 'not_as_described';

    public const REASON_ITEM_NOT_RECEIVED = 'item_not_received';

    public const REASON_PAYMENT_ISSUE = 'payment_issue';

    public const REASON_OTHER = 'other';

    public const SOLUTION_REFUND = 'refund';

    public const SOLUTION_RETURN_ITEM = 'return_item';

    public const SOLUTION_CANCEL_DEAL = 'cancel_deal';

    public const SOLUTION_OTHER = 'other';

    public const DECISION_FAVOR_BUYER = 'favor_buyer';

    public const DECISION_FAVOR_SELLER = 'favor_seller';

    public const DECISION_MUTUAL_AGREEMENT = 'mutual_agreement';

    public const DECISION_DISMISSED = 'dismissed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public const REASONS = [
        self::REASON_NOT_AS_DESCRIBED => 'Barang Tidak Sesuai Deskripsi / Cacat',
        self::REASON_ITEM_NOT_RECEIVED => 'Barang Tidak Diterima / Tidak Hadir Saat Janji Temu',
        self::REASON_PAYMENT_ISSUE => 'Kendala Pembayaran / Selisih Nominal',
        self::REASON_OTHER => 'Kendala Transaksi Lainnya',
    ];

    public const SOLUTIONS = [
        self::SOLUTION_REFUND => 'Pengembalian Dana Penuh',
        self::SOLUTION_RETURN_ITEM => 'Retur / Pengembalian Barang',
        self::SOLUTION_CANCEL_DEAL => 'Pembatalan Transaksi',
        self::SOLUTION_OTHER => 'Solusi Kesepakatan Bersama',
    ];

    public const DECISIONS = [
        self::DECISION_FAVOR_BUYER => 'Mediasi Memenangkan Pembeli (Pengembalian Dana Disetujui)',
        self::DECISION_FAVOR_SELLER => 'Mediasi Memenangkan Penjual (Transaksi Dinyatakan Sah)',
        self::DECISION_MUTUAL_AGREEMENT => 'Kesepakatan Damai Bersama',
        self::DECISION_DISMISSED => 'Laporan Ditolak / Tidak Ditemukan Pelanggaran',
    ];

    protected $fillable = [
        'product_id',
        'offer_id',
        'reporter_id',
        'respondent_id',
        'title',
        'reason',
        'description',
        'solution_requested',
        'status',
        'admin_id',
        'admin_decision',
        'admin_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Menunggu Mediasi',
            self::STATUS_UNDER_REVIEW => 'Sedang Ditinjau Admin',
            self::STATUS_RESOLVED => 'Mediasi Selesai',
            self::STATUS_CLOSED => 'Kasus Ditutup',
            default => ucfirst($this->status),
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function getSolutionLabelAttribute(): string
    {
        return self::SOLUTIONS[$this->solution_requested] ?? ucfirst(str_replace('_', ' ', $this->solution_requested));
    }

    public function getDecisionLabelAttribute(): ?string
    {
        if (! $this->admin_decision) {
            return null;
        }

        return self::DECISIONS[$this->admin_decision] ?? ucfirst(str_replace('_', ' ', $this->admin_decision));
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_UNDER_REVIEW], true);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
