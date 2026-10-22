<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Conversation: Fajar (buyer) asks Budi (seller) about Calculus book (Product 1)
        $budi = User::where('email', 'budi.santoso@students.undip.ac.id')->first();
        $fajar = User::where('email', 'fajar.nugroho@mail.ugm.ac.id')->first();
        $calculusProduct = Product::where('user_id', $budi?->id)->first();

        if ($budi && $fajar && $calculusProduct) {
            $conv1 = Conversation::updateOrCreate(
                [
                    'buyer_id' => $fajar->id,
                    'seller_id' => $budi->id,
                    'product_id' => $calculusProduct->id,
                ],
                [
                    'last_message_at' => now(),
                ]
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conv1->id,
                    'sender_id' => $fajar->id,
                    'body' => 'Halo Mas Budi, bukunya masih ready? Bisa COD di sekitar kampus?',
                ],
                [
                    'read_at' => now()->subMinutes(10),
                    'created_at' => now()->subMinutes(30),
                ]
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conv1->id,
                    'sender_id' => $budi->id,
                    'body' => 'Halo Fajar! Masih ready kok. Bisa COD di FSM Tembalang atau perpustakaan.',
                ],
                [
                    'read_at' => now()->subMinutes(5),
                    'created_at' => now()->subMinutes(20),
                ]
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conv1->id,
                    'sender_id' => $fajar->id,
                    'body' => 'Oke siap mas, kalau 50rb langsung saya ambil besok siang gimana?',
                ],
                [
                    'read_at' => null, // unread for Budi!
                    'created_at' => now()->subMinutes(2),
                ]
            );
        }

        // 2. Conversation: Budi (buyer) inquires about a product from another seller
        $otherProduct = Product::where('user_id', '!=', $budi?->id)->first();
        if ($budi && $otherProduct) {
            $conv2 = Conversation::updateOrCreate(
                [
                    'buyer_id' => $budi->id,
                    'seller_id' => $otherProduct->user_id,
                    'product_id' => $otherProduct->id,
                ],
                [
                    'last_message_at' => now()->subHours(1),
                ]
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conv2->id,
                    'sender_id' => $budi->id,
                    'body' => 'Permisi kak, mau tanya apakah barang ini masih ada?',
                ],
                [
                    'read_at' => now()->subHours(1),
                    'created_at' => now()->subHours(2),
                ]
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conv2->id,
                    'sender_id' => $otherProduct->user_id,
                    'body' => 'Halo Budi, iya masih ada ya silakan kalau mau tanya-tanya.',
                ],
                [
                    'read_at' => now()->subHours(1),
                    'created_at' => now()->subHours(1),
                ]
            );
        }
    }
}
