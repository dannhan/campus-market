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
        Schema::create('reward_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('points_cost');
            $table->string('reward_type')->default('discount_fixed'); // discount_fixed, free_shipping, merchandise, badge, other
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->integer('stock')->default(-1); // -1 = unlimited
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('validity_days')->default(30);
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_items');
    }
};
