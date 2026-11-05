<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_DISMISSED = 'dismissed';

    public const STATUS_ACTION_TAKEN = 'action_taken';

    public const REASONS = [
        'scam_fraud' => 'Dugaan Penipuan / Akun Palsu',
        'prohibited_item' => 'Barang Terlarang / Melanggar Aturan Kampus',
        'fake_images' => 'Foto Produk Palsu / Hasil Curian',
        'misleading_price' => 'Harga Tidak Wajar / Menyesatkan',
        'inappropriate' => 'Konten Tidak Pantas',
        'other' => 'Pelanggaran Lainnya',
    ];

    protected $fillable = [
        'product_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? 'Laporan Pelanggaran';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Tinjauan',
            self::STATUS_REVIEWED => 'Telah Ditinjau',
            self::STATUS_DISMISSED => 'Laporan Diabaikan',
            self::STATUS_ACTION_TAKEN => 'Tindakan Diambil',
            default => ucfirst($this->status),
        };
    }
}
