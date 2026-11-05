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
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reward_item_id')->constrained('reward_items')->cascadeOnDelete();
            $table->unsignedInteger('points_spent');
            $table->string('voucher_code')->unique();
            $table->string('status')->default('active'); // active, used, expired, fulfilled
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
