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
        Schema::table('universities', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('email_domain');
            $table->string('province', 100)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('province');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('address', 255)->nullable()->after('longitude');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('origin_university_id')
                ->nullable()
                ->after('delivery_method')
                ->constrained('universities')
                ->nullOnDelete();

            $table->foreignId('destination_university_id')
                ->nullable()
                ->after('origin_university_id')
                ->constrained('universities')
                ->nullOnDelete();

            $table->decimal('distance_km', 8, 2)->nullable()->after('destination_university_id');
            $table->string('estimated_delivery_days', 50)->nullable()->after('distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['origin_university_id']);
            $table->dropForeign(['destination_university_id']);
            $table->dropColumn([
                'origin_university_id',
                'destination_university_id',
                'distance_km',
                'estimated_delivery_days',
            ]);
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'province',
                'latitude',
                'longitude',
                'address',
            ]);
        });
    }
};
