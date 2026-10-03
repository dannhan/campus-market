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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->unique()->after('id')->constrained('students')->nullOnDelete();
            $table->string('avatar')->nullable()->after('email');
            $table->decimal('seller_rating', 3, 2)->nullable()->after('remember_token');
            $table->decimal('buyer_rating', 3, 2)->nullable()->after('seller_rating');
            $table->unsignedInteger('seller_review_count')->default(0)->after('buyer_rating');
            $table->unsignedInteger('buyer_review_count')->default(0)->after('seller_review_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->dropConstrainedForeignId('student_id');
            $table->dropColumn([
                'avatar',
                'seller_rating',
                'buyer_rating',
                'seller_review_count',
                'buyer_review_count',
            ]);
        });
    }
};
