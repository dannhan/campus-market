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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->enum('condition', ['new', 'like_new', 'used'])->default('used');
            $table->enum('transaction_type', ['sale', 'rent'])->default('sale');
            $table->enum('status', ['active', 'sold', 'withdrawn'])->default('active');
            $table->string('location')->nullable(); // Campus pickup point, e.g. "Kampus Tembalang"
            $table->boolean('bargainable')->default(true); // Whether the seller accepts price offers
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
