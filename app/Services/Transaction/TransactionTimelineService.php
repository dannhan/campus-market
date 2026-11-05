<?php

namespace App\Services\Transaction;

use App\Models\EscrowPayment;
use App\Models\Transaction;

class TransactionTimelineService
{
    /**
     * Build the structured visual timeline and progress tracker for a transaction.
     *
     * @return array{
     *     current_step: int,
     *     total_steps: int,
     *     progress_percentage: int,
     *     status: string,
     *     status_badge: string,
     *     status_title: string,
     *     status_description: string,
     *     is_completed: bool,
     *     is_cancelled: bool,
     *     is_disputed: bool,
     *     steps: array,
     *     events: array
     * }
     */
    public function getTimeline(Transaction $transaction): array
    {
        $isCancelled = $transaction->status === Transaction::STATUS_CANCELLED;
        $isCompleted = $transaction->status === Transaction::STATUS_COMPLETED;
        $isDisputed = ($transaction->escrowPayment && $transaction->escrowPayment->status === EscrowPayment::STATUS_DISPUTED);

        if ($transaction->delivery_method === Transaction::DELIVERY_METHOD_COD) {
            return $this->buildCodTimeline($transaction, $isCancelled, $isCompleted, $isDisputed);
        }

        if ($transaction->delivery_method === Transaction::DELIVERY_METHOD_PICKUP) {
            return $this->buildPickupTimeline($transaction, $isCancelled, $isCompleted, $isDisputed);
        }

        return $this->buildCourierTimeline($transaction, $isCancelled, $isCompleted, $isDisputed);
    }

    /**
     * Build timeline for Courier Delivery transactions.
     */
    protected function buildCourierTimeline(Transaction $transaction, bool $isCancelled, bool $isCompleted, bool $isDisputed): array
    {
        $escrow = $transaction->escrowPayment;
        $isEscrow = $transaction->use_escrow && $escrow;

        $hasPaymentProof = ! empty($transaction->payment_proof) || ($escrow && $escrow->proof_of_transfer);
        $isPaymentVerified = $isCompleted
            || ($escrow && in_array($escrow->status, [EscrowPayment::STATUS_HELD, EscrowPayment::STATUS_RELEASED], true))
            || $transaction->payment_status === Transaction::PAYMENT_STATUS_CONFIRMED;

        $isShipped = ! empty($transaction->shipped_at) || ! empty($transaction->tracking_number);
        $isDelivered = ! empty($transaction->delivered_at);

        // Determine current active step (1 to 5)
        if ($isCancelled) {
            $currentStep = 1;
            $progressPercentage = 100;
        } elseif ($isCompleted) {
            $currentStep = 5;
            $progressPercentage = 100;
        } elseif ($isDelivered) {
            $currentStep = 5; // Waiting for buyer escrow release / final confirmation
            $progressPercentage = 85;
        } elseif ($isShipped) {
            $currentStep = 4; // In delivery transit
            $progressPercentage = 65;
        } elseif ($isPaymentVerified) {
            $currentStep = 3; // Packing / waiting for seller to drop to courier
            $progressPercentage = 45;
        } elseif ($hasPaymentProof) {
            $currentStep = 2; // Payment uploaded, waiting verification
            $progressPercentage = 30;
        } else {
            $currentStep = 2; // Waiting payment
            $progressPercentage = 15;
        }

        // Step 1: Pesanan Dibuat
        $step1State = 'completed';
        $step1Desc = 'Pesanan dibuat oleh '.$transaction->buyer->name;

        // Step 2: Pembayaran & Rekber
        if ($isPaymentVerified) {
            $step2State = 'completed';
            $step2Desc = $isEscrow
                ? 'Dana transfer telah diamankan di rekening penampung resmi CampusMarket'
                : 'Pembayaran transfer telah diverifikasi';
        } elseif ($hasPaymentProof) {
            $step2State = 'current';
            $step2Desc = 'Bukti pembayaran telah diunggah, menunggu verifikasi rekening bersama';
        } else {
            $step2State = $isCancelled ? 'cancelled' : 'current';
            $step2Desc = 'Menunggu transfer pembayaran dari pembeli';
        }

        // Step 3: Pengiriman Kurir
        if ($isShipped) {
            $step3State = 'completed';
            $step3Desc = 'Paket diserahkan ke kurir '.$transaction->courier_name_label.($transaction->tracking_number ? ' (Resi: '.$transaction->tracking_number.')' : '');
        } elseif ($isPaymentVerified) {
            $step3State = $isCancelled ? 'cancelled' : 'current';
            $step3Desc = 'Penjual sedang menyiapkan barang & nomor resi pengiriman';
        } else {
            $step3State = 'upcoming';
            $step3Desc = 'Menunggu pembayaran terverifikasi sebelum dikirim';
        }

        // Step 4: Barang Diterima
        if ($isDelivered) {
            $step4State = 'completed';
            $step4Desc = 'Paket barang telah sampai dan diterima oleh pembeli';
        } elseif ($isShipped) {
            $step4State = $isCancelled ? 'cancelled' : 'current';
            $step4Desc = 'Paket dalam perjalanan kurir menuju alamat kos / tempat tinggal';
        } else {
            $step4State = 'upcoming';
            $step4Desc = 'Menunggu paket diserahkan ke kurir';
        }

        // Step 5: Selesai & Pencairan Dana
        if ($isCompleted) {
            $step5State = 'completed';
            $step5Desc = $isEscrow
                ? 'Dana rekber berhasil dicairkan ke rekening penjual. Transaksi sukses!'
                : 'Transaksi telah selesai diverifikasi';
        } elseif ($isDelivered) {
            $step5State = $isCancelled ? 'cancelled' : 'current';
            $step5Desc = 'Pembeli memeriksa barang dan mengonfirmasi pencairan dana rekber ke penjual';
        } else {
            $step5State = 'upcoming';
            $step5Desc = 'Menunggu konfirmasi penerimaan barang dari pembeli';
        }

        $steps = [
            [
                'index' => 1,
                'title' => 'Pesanan Dibuat',
                'state' => $step1State,
                'timestamp' => $transaction->created_at,
                'formatted_time' => $transaction->created_at->translatedFormat('d M Y, H:i').' WIB',
                'description' => $step1Desc,
                'actor' => 'Pembeli',
                'icon' => 'clipboard',
            ],
            [
                'index' => 2,
                'title' => $isEscrow ? 'Proteksi Rekber' : 'Pembayaran',
                'state' => $step2State,
                'timestamp' => $escrow?->verified_at ?? $transaction->paid_at,
                'formatted_time' => ($escrow?->verified_at ?? $transaction->paid_at)?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $step2Desc,
                'actor' => $isEscrow ? 'Admin Rekber' : 'Penjual',
                'icon' => 'shield',
            ],
            [
                'index' => 3,
                'title' => 'Kirim Ekspedisi',
                'state' => $step3State,
                'timestamp' => $transaction->shipped_at,
                'formatted_time' => $transaction->shipped_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $step3Desc,
                'actor' => 'Penjual / Kurir',
                'icon' => 'truck',
            ],
            [
                'index' => 4,
                'title' => 'Paket Tiba',
                'state' => $step4State,
                'timestamp' => $transaction->delivered_at,
                'formatted_time' => $transaction->delivered_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $step4Desc,
                'actor' => 'Pembeli',
                'icon' => 'box',
            ],
            [
                'index' => 5,
                'title' => 'Selesai',
                'state' => $step5State,
                'timestamp' => $transaction->confirmed_at ?? $escrow?->released_at,
                'formatted_time' => ($transaction->confirmed_at ?? $escrow?->released_at)?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $step5Desc,
                'actor' => 'Sistem CampusMarket',
                'icon' => 'check-circle',
            ],
        ];

        return [
            'current_step' => $currentStep,
            'total_steps' => 5,
            'progress_percentage' => $progressPercentage,
            'status' => $transaction->status,
            'status_badge' => $this->getStatusBadgeClass($transaction->status),
            'status_title' => $this->resolveStatusTitle($transaction, $isCancelled, $isCompleted, $isDisputed),
            'status_description' => $this->resolveStatusDescription($transaction, $isCancelled, $isCompleted, $isDisputed),
            'is_completed' => $isCompleted,
            'is_cancelled' => $isCancelled,
            'is_disputed' => $isDisputed,
            'steps' => $steps,
            'events' => $this->buildMilestoneEvents($transaction),
        ];
    }

    /**
     * Build timeline for Campus Pickup transactions.
     */
    protected function buildPickupTimeline(Transaction $transaction, bool $isCancelled, bool $isCompleted, bool $isDisputed): array
    {
        $escrow = $transaction->escrowPayment;
        $isEscrow = $transaction->use_escrow && $escrow;

        $isPaymentVerified = $isCompleted
            || ($escrow && in_array($escrow->status, [EscrowPayment::STATUS_HELD, EscrowPayment::STATUS_RELEASED], true))
            || $transaction->payment_status === Transaction::PAYMENT_STATUS_CONFIRMED
            || $transaction->payment_method === Transaction::PAYMENT_METHOD_COD;

        $isReadyForPickup = ! empty($transaction->shipped_at);
        $isPickedUp = ! empty($transaction->delivered_at);

        if ($isCancelled) {
            $currentStep = 1;
            $progressPercentage = 100;
        } elseif ($isCompleted) {
            $currentStep = 5;
            $progressPercentage = 100;
        } elseif ($isPickedUp) {
            $currentStep = 5;
            $progressPercentage = 85;
        } elseif ($isReadyForPickup) {
            $currentStep = 4;
            $progressPercentage = 65;
        } elseif ($isPaymentVerified) {
            $currentStep = 3;
            $progressPercentage = 45;
        } else {
            $currentStep = 2;
            $progressPercentage = 20;
        }

        $pickupLoc = $transaction->pickup_location ?: 'Titik kumpul kampus penjual';

        $steps = [
            [
                'index' => 1,
                'title' => 'Pesanan Dibuat',
                'state' => 'completed',
                'timestamp' => $transaction->created_at,
                'formatted_time' => $transaction->created_at->translatedFormat('d M Y, H:i').' WIB',
                'description' => 'Pesanan pickup mandiri dibuat oleh '.$transaction->buyer->name,
                'actor' => 'Pembeli',
                'icon' => 'clipboard',
            ],
            [
                'index' => 2,
                'title' => $isEscrow ? 'Dana Diamankan' : 'Konfirmasi',
                'state' => $isPaymentVerified ? 'completed' : ($isCancelled ? 'cancelled' : 'current'),
                'timestamp' => $escrow?->verified_at ?? $transaction->paid_at,
                'formatted_time' => ($escrow?->verified_at ?? $transaction->paid_at)?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isPaymentVerified
                    ? 'Dana rekber telah diamankan di penampung CampusMarket'
                    : 'Menunggu konfirmasi setoran pembayaran pembeli',
                'actor' => $isEscrow ? 'Admin Rekber' : 'Penjual',
                'icon' => 'shield',
            ],
            [
                'index' => 3,
                'title' => 'Siap di Titik Kampus',
                'state' => $isReadyForPickup ? 'completed' : ($isPaymentVerified ? ($isCancelled ? 'cancelled' : 'current') : 'upcoming'),
                'timestamp' => $transaction->shipped_at,
                'formatted_time' => $transaction->shipped_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isReadyForPickup
                    ? "Barang sudah diletakkan di titik kampus: {$pickupLoc}"
                    : "Penjual menyiapkan barang di titik pengambilan: {$pickupLoc}",
                'actor' => 'Penjual',
                'icon' => 'building',
            ],
            [
                'index' => 4,
                'title' => 'Barang Diambil',
                'state' => $isPickedUp ? 'completed' : ($isReadyForPickup ? ($isCancelled ? 'cancelled' : 'current') : 'upcoming'),
                'timestamp' => $transaction->delivered_at,
                'formatted_time' => $transaction->delivered_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isPickedUp
                    ? 'Barang telah berhasil diambil oleh pembeli di titik kampus'
                    : 'Pembeli mengambil barang di titik kampus yang ditentukan',
                'actor' => 'Pembeli',
                'icon' => 'box',
            ],
            [
                'index' => 5,
                'title' => 'Selesai',
                'state' => $isCompleted ? 'completed' : ($isPickedUp ? ($isCancelled ? 'cancelled' : 'current') : 'upcoming'),
                'timestamp' => $transaction->confirmed_at ?? $escrow?->released_at,
                'formatted_time' => ($transaction->confirmed_at ?? $escrow?->released_at)?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isCompleted
                    ? 'Transaksi selesai & dana rekber telah diteruskan ke rekening penjual'
                    : 'Menunggu konfirmasi selesai dan pencairan rekber',
                'actor' => 'Sistem CampusMarket',
                'icon' => 'check-circle',
            ],
        ];

        return [
            'current_step' => $currentStep,
            'total_steps' => 5,
            'progress_percentage' => $progressPercentage,
            'status' => $transaction->status,
            'status_badge' => $this->getStatusBadgeClass($transaction->status),
            'status_title' => $this->resolveStatusTitle($transaction, $isCancelled, $isCompleted, $isDisputed),
            'status_description' => $this->resolveStatusDescription($transaction, $isCancelled, $isCompleted, $isDisputed),
            'is_completed' => $isCompleted,
            'is_cancelled' => $isCancelled,
            'is_disputed' => $isDisputed,
            'steps' => $steps,
            'events' => $this->buildMilestoneEvents($transaction),
        ];
    }

    /**
     * Build timeline for COD (Ketemu Langsung di Kampus) transactions.
     */
    protected function buildCodTimeline(Transaction $transaction, bool $isCancelled, bool $isCompleted, bool $isDisputed): array
    {
        $hasMeetingSpot = ! empty($transaction->meeting_location);
        $isDelivered = ! empty($transaction->delivered_at);

        if ($isCancelled) {
            $currentStep = 1;
            $progressPercentage = 100;
        } elseif ($isCompleted) {
            $currentStep = 4;
            $progressPercentage = 100;
        } elseif ($isDelivered) {
            $currentStep = 3;
            $progressPercentage = 75;
        } else {
            $currentStep = 2;
            $progressPercentage = 40;
        }

        $meetingSpot = $transaction->meeting_location ?: 'Area kampus yang disepakati';

        $steps = [
            [
                'index' => 1,
                'title' => 'Pesanan COD Dibuat',
                'state' => 'completed',
                'timestamp' => $transaction->created_at,
                'formatted_time' => $transaction->created_at->translatedFormat('d M Y, H:i').' WIB',
                'description' => 'Pesanan COD diajukan oleh '.$transaction->buyer->name,
                'actor' => 'Pembeli',
                'icon' => 'clipboard',
            ],
            [
                'index' => 2,
                'title' => 'Titik Janji Temu',
                'state' => $hasMeetingSpot ? 'completed' : ($isCancelled ? 'cancelled' : 'current'),
                'timestamp' => $transaction->created_at,
                'formatted_time' => $transaction->created_at->translatedFormat('d M Y, H:i').' WIB',
                'description' => "Titik kumpul di kampus disepakati: {$meetingSpot}",
                'actor' => 'Penjual & Pembeli',
                'icon' => 'map-pin',
            ],
            [
                'index' => 3,
                'title' => 'Pertemuan & Cek Fisik',
                'state' => $isDelivered ? 'completed' : ($hasMeetingSpot ? ($isCancelled ? 'cancelled' : 'current') : 'upcoming'),
                'timestamp' => $transaction->delivered_at,
                'formatted_time' => $transaction->delivered_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isDelivered
                    ? 'Bertemu langsung di area kampus dan barang telah diperiksa pembeli'
                    : 'Menunggu pertemuan tatap muka di area kampus untuk memeriksa kondisi barang',
                'actor' => 'Penjual & Pembeli',
                'icon' => 'handshake',
            ],
            [
                'index' => 4,
                'title' => 'Bayar Tunai & Selesai',
                'state' => $isCompleted ? 'completed' : ($isDelivered ? ($isCancelled ? 'cancelled' : 'current') : 'upcoming'),
                'timestamp' => $transaction->confirmed_at,
                'formatted_time' => $transaction->confirmed_at?->translatedFormat('d M Y, H:i').' WIB',
                'description' => $isCompleted
                    ? 'Uang tunai diserahkan dan transaksi jual-beli telah dikonfirmasi selesai'
                    : 'Penjual menerima uang tunai pas dan mengonfirmasi penyelesaian transaksi',
                'actor' => 'Penjual & Pembeli',
                'icon' => 'check-circle',
            ],
        ];

        return [
            'current_step' => $currentStep,
            'total_steps' => 4,
            'progress_percentage' => $progressPercentage,
            'status' => $transaction->status,
            'status_badge' => $this->getStatusBadgeClass($transaction->status),
            'status_title' => $this->resolveStatusTitle($transaction, $isCancelled, $isCompleted, $isDisputed),
            'status_description' => $this->resolveStatusDescription($transaction, $isCancelled, $isCompleted, $isDisputed),
            'is_completed' => $isCompleted,
            'is_cancelled' => $isCancelled,
            'is_disputed' => $isDisputed,
            'steps' => $steps,
            'events' => $this->buildMilestoneEvents($transaction),
        ];
    }

    /**
     * Build chronological audit log of real events that have occurred.
     */
    protected function buildMilestoneEvents(Transaction $transaction): array
    {
        $events = [];
        $escrow = $transaction->escrowPayment;

        // 1. Order Placed
        $events[] = [
            'title' => 'Pesanan Berhasil Dibuat',
            'timestamp' => $transaction->created_at,
            'formatted_time' => $transaction->created_at->translatedFormat('d M Y, H:i').' WIB',
            'relative_time' => $transaction->created_at->diffForHumans(),
            'description' => "Pesanan #{$transaction->transaction_code} dibuat dengan total {$transaction->formatted_total_amount}.",
            'actor' => $transaction->buyer->name.' (Pembeli)',
            'icon' => 'clipboard',
            'type' => 'success',
        ];

        // 2. Payment Proof Uploaded
        if ($transaction->paid_at) {
            $events[] = [
                'title' => 'Bukti Transfer Pembayaran Diunggah',
                'timestamp' => $transaction->paid_at,
                'formatted_time' => $transaction->paid_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $transaction->paid_at->diffForHumans(),
                'description' => 'Pembeli telah mengunggah bukti transfer pembayaran rekening bersama.',
                'actor' => $transaction->buyer->name.' (Pembeli)',
                'icon' => 'cash',
                'type' => 'info',
            ];
        }

        // 3. Escrow Payment Verified by Admin
        if ($escrow && $escrow->verified_at) {
            $events[] = [
                'title' => 'Pembayaran Rekber Diverifikasi',
                'timestamp' => $escrow->verified_at,
                'formatted_time' => $escrow->verified_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $escrow->verified_at->diffForHumans(),
                'description' => "Dana sebesar {$escrow->formatted_total_amount} telah diverifikasi dan diamankan di rekening bersama CampusMarket.",
                'actor' => 'Admin Rekber CampusMarket',
                'icon' => 'shield',
                'type' => 'success',
            ];
        }

        // 4. Shipped / Ready for Pickup
        if ($transaction->shipped_at) {
            $isPickup = $transaction->delivery_method === Transaction::DELIVERY_METHOD_PICKUP;
            $events[] = [
                'title' => $isPickup ? 'Barang Siap di Titik Pickup Kampus' : 'Paket Diserahkan ke Kurir Ekspedisi',
                'timestamp' => $transaction->shipped_at,
                'formatted_time' => $transaction->shipped_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $transaction->shipped_at->diffForHumans(),
                'description' => $isPickup
                    ? "Penjual telah menyiapkan barang di: {$transaction->pickup_location}."
                    : "Paket dikirim via {$transaction->courier_name_label}".($transaction->tracking_number ? " dengan nomor resi {$transaction->tracking_number}." : '.'),
                'actor' => $transaction->seller->name.' (Penjual)',
                'icon' => $isPickup ? 'building' : 'truck',
                'type' => 'primary',
            ];
        }

        // 5. Delivered / Received
        if ($transaction->delivered_at) {
            $events[] = [
                'title' => 'Barang Diterima oleh Pembeli',
                'timestamp' => $transaction->delivered_at,
                'formatted_time' => $transaction->delivered_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $transaction->delivered_at->diffForHumans(),
                'description' => 'Pembeli mengonfirmasi bahwa fisik barang telah sampai dan diterima dengan baik.',
                'actor' => $transaction->buyer->name.' (Pembeli)',
                'icon' => 'box',
                'type' => 'success',
            ];
        }

        // 6. Confirmed Completed & Funds Released
        if ($transaction->confirmed_at) {
            $events[] = [
                'title' => 'Transaksi Selesai & Dana Dicairkan',
                'timestamp' => $transaction->confirmed_at,
                'formatted_time' => $transaction->confirmed_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $transaction->confirmed_at->diffForHumans(),
                'description' => $escrow
                    ? 'Dana rekber telah dilepaskan dan dicairkan ke rekening penjual. Transaksi jual-beli resmi selesai.'
                    : 'Transaksi jual-beli telah selesai dan dikonfirmasi oleh kedua belah pihak.',
                'actor' => 'Sistem CampusMarket',
                'icon' => 'check-circle',
                'type' => 'success',
            ];
        }

        // 7. Cancelled
        if ($transaction->cancelled_at) {
            $events[] = [
                'title' => 'Transaksi Dibatalkan',
                'timestamp' => $transaction->cancelled_at,
                'formatted_time' => $transaction->cancelled_at->translatedFormat('d M Y, H:i').' WIB',
                'relative_time' => $transaction->cancelled_at->diffForHumans(),
                'description' => 'Pesanan dibatalkan. Produk telah dikembalikan ke etalase toko.',
                'actor' => 'Pihak Transaksi',
                'icon' => 'x-circle',
                'type' => 'danger',
            ];
        }

        // Sort events chronologically descending (latest first)
        usort($events, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $events;
    }

    protected function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            Transaction::STATUS_PENDING => 'bg-amber-100 text-amber-800 border-amber-200',
            Transaction::STATUS_PROCESSING => 'bg-blue-100 text-blue-800 border-blue-200',
            Transaction::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            Transaction::STATUS_CANCELLED => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    protected function resolveStatusTitle(Transaction $transaction, bool $isCancelled, bool $isCompleted, bool $isDisputed): string
    {
        if ($isCancelled) {
            return 'Transaksi Dibatalkan';
        }
        if ($isDisputed) {
            return 'Dalam Mediasi Pusat Bantuan (Dispute)';
        }
        if ($isCompleted) {
            return 'Transaksi Selesai & Berhasil';
        }

        if ($transaction->delivery_method === Transaction::DELIVERY_METHOD_COD) {
            if ($transaction->delivered_at) {
                return 'Menunggu Konfirmasi Pembayaran Tunai';
            }

            return 'Menunggu Pertemuan COD di Kampus';
        }

        if (! empty($transaction->delivered_at)) {
            return 'Barang Diterima, Menunggu Konfirmasi Selesai';
        }

        if (! empty($transaction->shipped_at)) {
            return $transaction->delivery_method === Transaction::DELIVERY_METHOD_PICKUP
                ? 'Barang Siap Diambil di Titik Kampus'
                : 'Paket Sedang Dalam Pengiriman Kurir';
        }

        $escrow = $transaction->escrowPayment;
        if ($escrow && in_array($escrow->status, [EscrowPayment::STATUS_HELD, EscrowPayment::STATUS_RELEASED], true)) {
            return 'Dana Rekber Aman, Penjual Menyiapkan Barang';
        }

        if (! empty($transaction->payment_proof) || ($escrow && $escrow->proof_of_transfer)) {
            return 'Bukti Diunggah, Menunggu Verifikasi Pembayaran';
        }

        return 'Menunggu Pembayaran Pembeli';
    }

    protected function resolveStatusDescription(Transaction $transaction, bool $isCancelled, bool $isCompleted, bool $isDisputed): string
    {
        if ($isCancelled) {
            return 'Transaksi ini telah dibatalkan dan produk telah dikembalikan ke etalase toko.';
        }
        if ($isDisputed) {
            return 'Transaksi sedang ditinjau oleh Admin CampusMarket untuk mediasi penyelesaian kendala.';
        }
        if ($isCompleted) {
            return 'Seluruh tahapan transaksi telah tuntas dengan aman. Terima kasih telah bertransaksi di CampusMarket!';
        }

        if ($transaction->delivery_method === Transaction::DELIVERY_METHOD_COD) {
            return 'Silakan bertemu langsung di titik janji temu kampus untuk memeriksa barang dan menyerahkan uang tunai.';
        }

        if (! empty($transaction->delivered_at)) {
            return 'Barang telah diterima. Silakan periksa kondisi barang dan konfirmasi penyelesaian agar dana diteruskan ke penjual.';
        }

        if (! empty($transaction->shipped_at)) {
            return $transaction->delivery_method === Transaction::DELIVERY_METHOD_PICKUP
                ? 'Silakan ambil pesanan Anda pada titik kumpul kampus yang telah ditentukan penjual.'
                : 'Paket telah diserahkan ke kurir pengiriman. Pantau status paket menggunakan nomor resi yang tertera.';
        }

        $escrow = $transaction->escrowPayment;
        if ($escrow && in_array($escrow->status, [EscrowPayment::STATUS_HELD, EscrowPayment::STATUS_RELEASED], true)) {
            return 'Pembayaran Anda sudah diverifikasi aman oleh CampusMarket. Penjual sedang menyiapkan barang Anda.';
        }

        if (! empty($transaction->payment_proof) || ($escrow && $escrow->proof_of_transfer)) {
            return 'Bukti transfer sedang diperiksa oleh verifikator resmi CampusMarket.';
        }

        return 'Silakan lakukan pembayaran sesuai instruksi agar pesanan dapat segera diproses oleh penjual.';
    }
}
