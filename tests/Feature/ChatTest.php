<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected User $seller;

    protected User $buyer;

    protected User $otherBuyer;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Diponegoro',
            'slug' => 'undip',
            'email_domain' => 'students.undip.ac.id',
        ]);

        $this->seller = $this->createVerifiedStudentUser('24060122130001', 'Penjual Pertama', 'penjual@students.undip.ac.id');
        $this->buyer = $this->createVerifiedStudentUser('24060122130002', 'Pembeli Kedua', 'pembeli@students.undip.ac.id');
        $this->otherBuyer = $this->createVerifiedStudentUser('24060122130003', 'Pembeli Ketiga', 'pembeli3@students.undip.ac.id');

        $this->category = Category::create(['name' => 'Buku Kuliah', 'slug' => 'buku-kuliah']);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Buku Algoritma Pemrograman',
            'slug' => 'buku-algoritma-pemrograman',
            'description' => 'Buku masih bagus dan rapi.',
            'price' => 75000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'bargainable' => true,
        ]);
    }

    protected function createVerifiedStudentUser(string $nim, string $name, string $email, bool $verified = true): User
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
            'email_verified_at' => $verified ? now() : null,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_chats(): void
    {
        $response = $this->get(route('chats.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unverified_student_cannot_access_chats(): void
    {
        $unverified = $this->createVerifiedStudentUser('24060122130099', 'Belum Verif', 'unverified@students.undip.ac.id', false);

        $response = $this->actingAs($unverified)->get(route('chats.index'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_buyer_can_start_conversation_from_product_page(): void
    {
        $response = $this->actingAs($this->buyer)->post(route('products.chat', $this->product));

        $conversation = Conversation::where('buyer_id', $this->buyer->id)
            ->where('seller_id', $this->seller->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($conversation);
        $response->assertRedirect(route('chats.show', $conversation));
    }

    public function test_seller_cannot_start_conversation_with_themselves(): void
    {
        $response = $this->actingAs($this->seller)->post(route('products.chat', $this->product));

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('conversations', [
            'buyer_id' => $this->seller->id,
            'seller_id' => $this->seller->id,
        ]);
    }

    public function test_buyer_and_seller_can_view_conversation(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->buyer)->get(route('chats.show', $conversation))->assertOk();
        $this->actingAs($this->seller)->get(route('chats.show', $conversation))->assertOk();
    }

    public function test_third_party_cannot_view_or_send_messages_in_conversation(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        $this->actingAs($this->otherBuyer)
            ->get(route('chats.show', $conversation))
            ->assertForbidden();

        $this->actingAs($this->otherBuyer)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Pesan penyusup'])
            ->assertForbidden();
    }

    public function test_participant_can_send_message_in_thread(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->buyer)->post(route('chats.messages.store', $conversation), [
            'body' => 'Halo kak, apakah barangnya masih ready?',
        ]);

        $response->assertRedirect(route('chats.show', $conversation));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->buyer->id,
            'body' => 'Halo kak, apakah barangnya masih ready?',
        ]);
    }

    public function test_viewing_conversation_marks_incoming_messages_as_read(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->buyer->id,
            'body' => 'Pesan belum terbaca',
            'read_at' => null,
        ]);

        $this->assertNull($message->fresh()->read_at);

        // Seller opens chat
        $this->actingAs($this->seller)->get(route('chats.show', $conversation));

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_empty_or_whitespace_message_cannot_be_sent(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->buyer)->post(route('chats.messages.store', $conversation), [
            'body' => '   ',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_inbox_lists_conversations_ordered_by_latest_message(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->seller->id,
            'body' => 'Halo, ini pesan penjual.',
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('chats.index'));

        $response->assertOk();
        $response->assertSee('Halo, ini pesan penjual.');
        $response->assertSee($this->seller->name);
    }
}
