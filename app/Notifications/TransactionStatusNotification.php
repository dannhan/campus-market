<?php

namespace App\Notifications;

use App\Models\Transaction;
use App\Models\User;
use App\Services\Notification\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Transaction $transaction,
        public string $event,
        public ?string $customMessage = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification stored in database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $code = $this->transaction->transaction_code;
        $title = "Update Transaksi #{$code}";

        $body = $this->customMessage ?? match ($this->event) {
            'paid' => "Pembeli telah mengunggah bukti pembayaran untuk transaksi {$code}.",
            'verified' => "Pembayaran transaksi {$code} berhasil diverifikasi. Siapkan pesanan untuk dikirim.",
            'shipped' => "Pesanan {$code} telah dikirim".($this->transaction->courier_name ? " via {$this->transaction->courier_label}" : '').'.',
            'delivered' => "Pesanan {$code} telah tiba di lokasi tujuan/kampus.",
            'completed' => "Transaksi {$code} telah selesai tuntas. Terima kasih!",
            'cancelled' => "Transaksi {$code} telah dibatalkan.",
            default => "Status transaksi {$code} telah diperbarui menjadi: {$this->transaction->status_label}.",
        };

        $url = route('transactions.show', $this->transaction);

        if ($notifiable instanceof User) {
            app(PushNotificationService::class)->sendToUser($notifiable, [
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'type' => 'transaction_status',
                'tag' => "transaction-{$this->transaction->id}",
                'data' => [
                    'transaction_id' => $this->transaction->id,
                    'event' => $this->event,
                    'status' => $this->transaction->status,
                ],
            ]);
        }

        return [
            'type' => 'transaction_status',
            'event' => $this->event,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => 'transaction',
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $code,
            'status' => $this->transaction->status,
            'amount' => $this->transaction->total_amount,
        ];
    }
}
