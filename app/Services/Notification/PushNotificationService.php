<?php

namespace App\Services\Notification;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing Web Push API subscriptions and dispatching push notifications.
 */
class PushNotificationService
{
    /**
     * In-memory storage for push notifications sent during testing.
     *
     * @var array<int, array{user_id: int, title: string, body: string, url: string, type: string, payload: array}>
     */
    protected static array $dispatchedPushNotifications = [];

    /**
     * Whether push sending is in fake/spy mode for tests.
     */
    protected static bool $isFaked = false;

    /**
     * Enable fake mode for testing assertions.
     */
    public static function fake(): void
    {
        self::$isFaked = true;
        self::$dispatchedPushNotifications = [];
    }

    /**
     * Clear fake recorded dispatches.
     */
    public static function resetFake(): void
    {
        self::$isFaked = false;
        self::$dispatchedPushNotifications = [];
    }

    /**
     * Get all dispatched push notifications during testing.
     *
     * @return array<int, array{user_id: int, title: string, body: string, url: string, type: string, payload: array}>
     */
    public static function dispatched(): array
    {
        return self::$dispatchedPushNotifications;
    }

    /**
     * Check if a push notification was dispatched for a specific user and type.
     */
    public static function hasDispatchedFor(User $user, ?string $type = null): bool
    {
        foreach (self::$dispatchedPushNotifications as $push) {
            if ($push['user_id'] === $user->id) {
                if ($type === null || ($push['type'] ?? null) === $type) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Register or update a browser push subscription for a user.
     *
     * @param  array{endpoint: string, public_key?: ?string, auth_token?: ?string, content_encoding?: ?string}  $data
     */
    public function subscribe(User $user, array $data, ?string $userAgent = null): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $user->id,
                'public_key' => $data['public_key'] ?? null,
                'auth_token' => $data['auth_token'] ?? null,
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
                'user_agent' => $userAgent,
            ]
        );
    }

    /**
     * Remove a browser push subscription.
     */
    public function unsubscribe(User $user, string $endpoint): bool
    {
        return (bool) PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->delete();
    }

    /**
     * Dispatch a push notification to all active devices of a user.
     *
     * @param  array{title: string, body: string, url?: string, type?: string, icon?: string, badge?: string, data?: array}  $payload
     * @return int Number of devices targeted
     */
    public function sendToUser(User $user, array $payload): int
    {
        $title = $payload['title'] ?? 'CampusMarket';
        $body = $payload['body'] ?? '';
        $url = $payload['url'] ?? url('/');
        $type = $payload['type'] ?? 'general';

        $record = [
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'type' => $type,
            'payload' => $payload,
            'created_at' => now(),
        ];

        self::$dispatchedPushNotifications[] = $record;

        $subscriptions = $user->pushSubscriptions()->get();

        Log::info("Push notification queued for User {$user->id} ({$user->name}): '{$title}' to {$subscriptions->count()} devices.", [
            'type' => $type,
            'url' => $url,
        ]);

        return $subscriptions->count();
    }
}
