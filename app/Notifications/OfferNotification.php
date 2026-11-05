<?php

namespace App\Notifications;

use App\Models\Offer;
use App\Models\User;
use App\Services\Notification\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Offer $offer,
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
        $product = $this->offer->product;
        $productTitle = $product?->title ?? 'Produk';
        $formattedPrice = 'Rp '.number_format($this->offer->offered_price, 0, ',', '.');
        $senderName = $this->offer->user?->name ?? 'Mahasiswa';

        $title = match ($this->event) {
            'new_offer' => 'Penawaran Harga Baru',
            'accepted' => 'Penawaran Harga Diterima! 🎉',
            'rejected' => 'Penawaran Harga Ditolak',
            'countered' => 'Tawaran Balasan (Counter Offer)',
            default => 'Update Penawaran Harga',
        };

        $body = $this->customMessage ?? match ($this->event) {
            'new_offer' => "{$senderName} menawar '{$productTitle}' seharga {$formattedPrice}.",
            'accepted' => "Penjual menerima tawaran Anda seharga {$formattedPrice} untuk '{$productTitle}'.",
            'rejected' => "Penawaran harga Anda sebesar {$formattedPrice} untuk '{$productTitle}' ditolak penjual.",
            'countered' => "Anda menerima tawaran balasan seharga {$formattedPrice} untuk '{$productTitle}'.",
            default => "Status penawaran untuk '{$productTitle}' telah diperbarui.",
        };

        $url = in_array($this->event, ['new_offer', 'countered'], true)
            ? route('offers.index')
            : route('products.show', $product);

        if ($notifiable instanceof User) {
            app(PushNotificationService::class)->sendToUser($notifiable, [
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'type' => 'offer',
                'tag' => "offer-{$this->offer->id}",
                'data' => [
                    'offer_id' => $this->offer->id,
                    'product_id' => $this->offer->product_id,
                    'event' => $this->event,
                ],
            ]);
        }

        return [
            'type' => 'offer',
            'event' => $this->event,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => 'offer',
            'offer_id' => $this->offer->id,
            'product_id' => $this->offer->product_id,
            'product_title' => $productTitle,
            'offered_price' => $this->offer->offered_price,
        ];
    }
}
