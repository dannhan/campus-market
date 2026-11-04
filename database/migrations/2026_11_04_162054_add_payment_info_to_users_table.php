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
            $table->string('bank_name', 50)->nullable()->after('buyer_review_count');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name', 100)->nullable()->after('bank_account_number');
            $table->string('ewallet_provider', 50)->nullable()->after('bank_account_name');
            $table->string('ewallet_account_number', 50)->nullable()->after('ewallet_provider');
            $table->string('ewallet_account_name', 100)->nullable()->after('ewallet_account_number');
            $table->string('preferred_meeting_location', 255)->nullable()->after('ewallet_account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'ewallet_provider',
                'ewallet_account_number',
                'ewallet_account_name',
                'preferred_meeting_location',
            ]);
        });
    }
};
