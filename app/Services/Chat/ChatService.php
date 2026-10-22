<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for real-time / direct chat between student buyer and seller (SRS-016).
 */
class ChatService
{
    /**
     * Retrieve or start a conversation about a specific product.
     *
     * @throws InvalidArgumentException
     */
    public function getOrCreateConversation(User $buyer, Product $product): Conversation
    {
        if ($product->user_id === $buyer->id) {
            throw new InvalidArgumentException('Anda tidak dapat memulai obrolan pada produk milik sendiri.');
        }

        /** @var Conversation $conversation */
        $conversation = Conversation::firstOrCreate(
            [
                'buyer_id' => $buyer->id,
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        return $conversation;
    }

    /**
     * Send a text message inside a conversation thread.
     *
     * @throws InvalidArgumentException
     */
    public function sendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        if (! $conversation->isParticipant($sender)) {
            throw new InvalidArgumentException('Anda bukan partisipan dalam percakapan ini.');
        }

        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Isi pesan tidak boleh kosong.');
        }

        return DB::transaction(function () use ($conversation, $sender, $trimmed) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $trimmed,
                'read_at' => null,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });
    }

    /**
     * Mark all incoming unread messages as read for the viewing user.
     */
    public function markAsRead(Conversation $conversation, User $reader): int
    {
        if (! $conversation->isParticipant($reader)) {
            return 0;
        }

        return $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * List user conversations with relations, ordered by latest message.
     */
    public function getUserConversations(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Conversation::query()
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->with([
                'product.images',
                'buyer.student.university',
                'seller.student.university',
                'latestMessage',
            ])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }
}
