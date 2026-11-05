<?php

namespace App\Http\Controllers;

use App\Services\Notification\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Display student's notifications inbox.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('tab', 'all');

        $query = $user->notifications();

        if ($filter === 'messages') {
            $query->where('data->type', 'new_message');
        } elseif ($filter === 'transactions') {
            $query->where('data->type', 'transaction_status');
        } elseif ($filter === 'offers') {
            $query->where('data->type', 'offer');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotificationsCount();
        $hasPushSubscriptions = $user->pushSubscriptions()->exists();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $unreadCount,
            'hasPushSubscriptions' => $hasPushSubscriptions,
            'totalCount' => $user->notifications()->count(),
            'messagesCount' => $user->notifications()->where('data->type', 'new_message')->count(),
            'transactionsCount' => $user->notifications()->where('data->type', 'transaction_status')->count(),
            'offersCount' => $user->notifications()->where('data->type', 'offer')->count(),
        ]);
    }

    /**
     * Mark a single notification as read and redirect to its action URL.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotificationsCount(),
            ]);
        }

        $url = $notification->data['url'] ?? route('notifications.index');

        return redirect()->to($url);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Store or update a browser push subscription.
     */
    public function subscribePush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string', 'max:50'],
        ]);

        $this->pushService->subscribe(
            $request->user(),
            $validated,
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi push berhasil diaktifkan pada perangkat ini.',
        ]);
    }

    /**
     * Remove a browser push subscription.
     */
    public function unsubscribePush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $this->pushService->unsubscribe($request->user(), $validated['endpoint']);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi push berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Send a test push notification to the authenticated user.
     */
    public function sendTestPush(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $count = $this->pushService->sendToUser($user, [
            'title' => 'Tes Notifikasi Push Kampus',
            'body' => "Halo {$user->name}! Sistem notifikasi push CampusMarket aktif dan siap menerima update transaksi, pesan, & tawaran.",
            'url' => route('notifications.index'),
            'type' => 'test',
            'tag' => 'test-push-'.time(),
        ]);

        $message = $count > 0
            ? "Tes notifikasi push berhasil dikirim ke {$count} perangkat Anda."
            : 'Tes notifikasi disimulasikan berhasil (belum ada izin perangkat aktif terdaftar).';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'devices_count' => $count,
            ]);
        }

        return back()->with('status', $message);
    }
}
