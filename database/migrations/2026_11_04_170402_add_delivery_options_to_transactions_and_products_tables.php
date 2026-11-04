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
        Schema::table('products', function (Blueprint $table) {
            $table->json('delivery_methods')->nullable()->after('bargainable');
            $table->string('pickup_location')->nullable()->after('delivery_methods');
            $table->decimal('courier_fee', 12, 2)->default(10000.00)->after('pickup_location');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_pickup_location')->nullable()->after('preferred_meeting_location');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('delivery_method', 30)->default('cod')->after('amount');
            $table->decimal('shipping_cost', 12, 2)->default(0.00)->after('delivery_method');
            $table->decimal('total_amount', 12, 2)->default(0.00)->after('shipping_cost');
            $table->string('pickup_location')->nullable()->after('meeting_location');
            $table->string('shipping_recipient_name', 100)->nullable()->after('pickup_location');
            $table->string('shipping_phone', 30)->nullable()->after('shipping_recipient_name');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->string('courier_name', 50)->nullable()->after('shipping_address');
            $table->string('tracking_number', 100)->nullable()->after('courier_name');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');

            $table->index('delivery_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['delivery_methods', 'pickup_location', 'courier_fee']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferred_pickup_location']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['delivery_method']);
            $table->dropColumn([
                'delivery_method',
                'shipping_cost',
                'total_amount',
                'pickup_location',
                'shipping_recipient_name',
                'shipping_phone',
                'shipping_address',
                'courier_name',
                'tracking_number',
                'shipped_at',
                'delivered_at',
            ]);
        });
    }
};
