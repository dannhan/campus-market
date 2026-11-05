<?php

namespace Tests\Feature\Notification;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Offer;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Notifications\OfferNotification;
use App\Notifications\TransactionStatusNotification;
use App\Services\Notification\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $seller;

    protected User $buyer;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        PushNotificationService::fake();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
        ]);

        $this->seller = $this->createVerifiedStudentUser('24060122130001', 'Budi Penjual', 'budi@students.undip.ac.id');
        $this->buyer = $this->createVerifiedStudentUser('24060122130002', 'Siti Pembeli', 'siti@students.undip.ac.id');

        $this->category = Category::create([
            'name' => 'Buku Kuliah',
            'slug' => 'buku-kuliah',
            'icon' => 'book',
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Kalkulus Purcell Edisi 9',
            'slug' => 'buku-kalkulus-purcell-edisi-9',
            'description' => 'Buku kalkulus lengkap catatan kuliah.',
            'price' => 80000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'bargainable' => true,
            'status' => 'active',
            'location' => 'Kampus Pleburan',
        ]);
    }

    protected function tearDown(): void
    {
        PushNotificationService::resetFake();
        parent::tearDown();
    }

    protected function createVerifiedStudentUser(string $nim, string $name, string $email): User
    {
        $student = Student::create([
            'university_id' => $this->university->id,
            'student_number' => $nim,
            'full_name' => $name,
            'major' => 'Informatika',
            'university_email' => $email,
            'is_active' => true,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('Password123!'),
            'student_id' => $student->id,
            'student_status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function test_user_can_subscribe_and_unsubscribe_to_push_notifications(): void
    {
        // 1. Subscribe device
        $response = $this->actingAs($this->buyer)
            ->postJson(route('push.subscribe'), [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-endpoint-12345',
                'public_key' => 'BB1234567890p256dhkey',
                'auth_token' => 'authSecret123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->buyer->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-endpoint-12345',
        ]);

        // 2. Unsubscribe device
        $unsubResponse = $this->actingAs($this->buyer)
            ->deleteJson(route('push.unsubscribe'), [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-endpoint-12345',
            ]);

        $unsubResponse->assertStatus(200);
        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-endpoint-12345',
        ]);
    }

    public function test_push_notification_is_sent_when_receiving_new_chat_message(): void
    {
        // Setup subscription for seller
        PushSubscription::create([
            'user_id' => $this->seller->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/seller-device-01',
        ]);

        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        // Buyer sends chat message
        $response = $this->actingAs($this->buyer)
            ->post(route('chats.messages.store', $conversation), [
                'body' => 'Halo kak, apakah bukunya masih ready untuk COD?',
            ]);

        $response->assertRedirect(route('chats.show', $conversation));

        // Assert database notification received by seller
        $this->assertEquals(1, $this->seller->notifications()->count());
        $notification = $this->seller->notifications()->first();
        $this->assertEquals(NewMessageNotification::class, $notification->type);
        $this->assertEquals('new_message', $notification->data['type']);
        $this->assertStringContainsString('Siti Pembeli', $notification->data['title']);

        // Assert push notification was dispatched
        $this->assertTrue(PushNotificationService::hasDispatchedFor($this->seller, 'new_message'));
        $dispatched = PushNotificationService::dispatched();
        $this->assertCount(1, $dispatched);
        $this->assertEquals($this->seller->id, $dispatched[0]['user_id']);
        $this->assertStringContainsString('Siti Pembeli', $dispatched[0]['title']);
        $this->assertStringContainsString('Halo kak, apakah bukunya masih ready', $dispatched[0]['body']);
    }

    public function test_push_notification_is_sent_when_new_offer_is_made(): void
    {
        // Buyer submits offer on seller's product
        $response = $this->actingAs($this->buyer)
            ->post(route('offers.store', $this->product), [
                'offered_price' => 70000,
                'message' => 'Boleh tawar 70rb ya kak, sesama anak informatika.',
            ]);

        $response->assertRedirect();

        // Seller receives offer notification
        $this->assertEquals(1, $this->seller->notifications()->count());
        $notification = $this->seller->notifications()->first();
        $this->assertEquals(OfferNotification::class, $notification->type);
        $this->assertEquals('offer', $notification->data['type']);
        $this->assertEquals('new_offer', $notification->data['event']);

        // Push notification verification
        $this->assertTrue(PushNotificationService::hasDispatchedFor($this->seller, 'offer'));
        $dispatched = PushNotificationService::dispatched();
        $this->assertCount(1, $dispatched);
        $this->assertStringContainsString('Penawaran Harga Baru', $dispatched[0]['title']);
        $this->assertStringContainsString('Rp 70.000', $dispatched[0]['body']);
    }

    public function test_push_notification_is_sent_when_offer_is_responded(): void
    {
        $offer = Offer::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'offered_price' => 65000,
            'message' => 'Nego tipis ya kak.',
            'status' => 'pending',
        ]);

        // 1. Counter offer by seller
        $response = $this->actingAs($this->seller)
            ->post(route('offers.respond', $offer), [
                'action' => 'counter',
                'offered_price' => 72000,
                'message' => 'Gimana kalau pas 72rb kak?',
            ]);

        $response->assertRedirect(route('offers.index'));

        // Buyer receives counter-offer notification
        $this->assertEquals(1, $this->buyer->notifications()->count());
        $notif = $this->buyer->notifications()->first();
        $this->assertEquals('countered', $notif->data['event']);
        $this->assertTrue(PushNotificationService::hasDispatchedFor($this->buyer, 'offer'));
    }

    public function test_push_notification_is_sent_when_transaction_status_changes(): void
    {
        // Create completed transaction
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-NOTIF-001',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 80000,
            'shipping_cost' => 0,
            'total_amount' => 80000,
            'payment_method' => Transaction::PAYMENT_METHOD_BANK_TRANSFER,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        // Seller marks item shipped
        $response = $this->actingAs($this->seller)
            ->post(route('transactions.ship', $transaction), [
                'courier_name' => 'jnt',
                'tracking_number' => 'JNTCAMPUS999888',
                'seller_notes' => 'Paket sudah diserahkan ke kurir.',
            ]);

        $response->assertRedirect();

        // Buyer receives transaction_status notification
        $this->assertEquals(1, $this->buyer->notifications()->count());
        $notification = $this->buyer->notifications()->first();
        $this->assertEquals(TransactionStatusNotification::class, $notification->type);
        $this->assertEquals('shipped', $notification->data['event']);
        $this->assertStringContainsString('TRX-NOTIF-001', $notification->data['title']);

        // Assert push notification was dispatched
        $this->assertTrue(PushNotificationService::hasDispatchedFor($this->buyer, 'transaction_status'));
    }

    public function test_notification_inbox_can_be_viewed_and_filtered(): void
    {
        // Seed 3 different notifications for the student
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);
        $message = $conversation->messages()->create([
            'sender_id' => $this->seller->id,
            'body' => 'Buku siap diambil besok pagi ya.',
        ]);
        $this->buyer->notify(new NewMessageNotification($message, $conversation));

        $transaction = Transaction::create([
            'transaction_code' => 'TRX-FILTER-001',
            'product_id' => $this->product->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount' => 80000,
            'shipping_cost' => 0,
            'total_amount' => 80000,
            'payment_method' => Transaction::PAYMENT_METHOD_COD,
            'delivery_method' => Transaction::DELIVERY_METHOD_PICKUP,
            'status' => Transaction::STATUS_PROCESSING,
        ]);
        $this->buyer->notify(new TransactionStatusNotification($transaction, 'paid'));

        // View inbox
        $response = $this->actingAs($this->buyer)
            ->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Notifikasi');
        $response->assertSee('Buku siap diambil besok pagi ya.');
        $response->assertSee('TRX-FILTER-001');

        // View filtered tab: messages
        $messagesTab = $this->actingAs($this->buyer)
            ->get(route('notifications.index', ['tab' => 'messages']));

        $messagesTab->assertStatus(200);
        $messagesTab->assertSee('Buku siap diambil besok pagi ya.');
        $messagesTab->assertDontSee('TRX-FILTER-001');
    }

    public function test_user_can_mark_single_and_all_notifications_as_read(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);
        $msg = $conversation->messages()->create([
            'sender_id' => $this->seller->id,
            'body' => 'Pesan belum terbaca',
        ]);
        $this->buyer->notify(new NewMessageNotification($msg, $conversation));

        $this->assertEquals(1, $this->buyer->unreadNotificationsCount());

        $notification = $this->buyer->unreadNotifications()->first();

        // Mark single as read
        $response = $this->actingAs($this->buyer)
            ->post(route('notifications.mark-read', $notification->id));

        $response->assertRedirect(route('chats.show', $conversation));
        $this->assertEquals(0, $this->buyer->unreadNotificationsCount());

        // Notify again & test mark all as read
        $this->buyer->notify(new NewMessageNotification($msg, $conversation));
        $this->assertEquals(1, $this->buyer->unreadNotificationsCount());

        $readAllResponse = $this->actingAs($this->buyer)
            ->post(route('notifications.read-all'));

        $readAllResponse->assertRedirect();
        $this->assertEquals(0, $this->buyer->unreadNotificationsCount());
    }

    public function test_user_can_trigger_test_push_notification(): void
    {
        $response = $this->actingAs($this->buyer)
            ->postJson(route('notifications.test-push'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertTrue(PushNotificationService::hasDispatchedFor($this->buyer, 'test'));
    }
}
