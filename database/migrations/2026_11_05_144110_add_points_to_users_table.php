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
            $table->unsignedInteger('points_balance')->default(0)->after('buyer_review_count');
            $table->unsignedInteger('points_lifetime')->default(0)->after('points_balance');
            $table->timestamp('last_check_in_at')->nullable()->after('points_lifetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points_balance', 'points_lifetime', 'last_check_in_at']);
        });
    }
};
