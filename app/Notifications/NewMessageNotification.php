<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Notification\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Message $message,
        public Conversation $conversation
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
        $senderName = $this->message->sender?->name ?? 'Pengguna';
        $productTitle = $this->conversation->product?->title;

        $title = "Pesan Baru dari {$senderName}";
        $body = Str::limit($this->message->body, 120);
        $url = route('chats.show', $this->conversation);

        if ($notifiable instanceof User) {
            app(PushNotificationService::class)->sendToUser($notifiable, [
                'title' => $title,
                'body' => $productTitle ? "[{$productTitle}] {$body}" : $body,
                'url' => $url,
                'type' => 'new_message',
                'tag' => "chat-{$this->conversation->id}",
                'data' => [
                    'conversation_id' => $this->conversation->id,
                    'message_id' => $this->message->id,
                ],
            ]);
        }

        return [
            'type' => 'new_message',
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => 'chat',
            'conversation_id' => $this->conversation->id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $senderName,
            'product_title' => $productTitle,
        ];
    }
}
