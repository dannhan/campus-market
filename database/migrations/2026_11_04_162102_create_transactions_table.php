<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 32)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30); // cod, bank_transfer, e_wallet
            $table->string('payment_channel', 30)->nullable(); // cod, bca, mandiri, bni, bri, bsi, gopay, ovo, dana, shopeepay
            $table->string('payment_status', 30)->default('pending'); // pending, paid, confirmed, cancelled
            $table->string('status', 30)->default('pending'); // pending, processing, completed, cancelled
            $table->string('meeting_location')->nullable();
            $table->string('payment_proof')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('buyer_notes')->nullable();
            $table->text('seller_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
