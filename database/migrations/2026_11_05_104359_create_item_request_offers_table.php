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
        Schema::create('item_request_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_request_id')->constrained('item_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('price', 12, 2);
            $table->text('message');
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['item_request_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_request_offers');
    }
};
