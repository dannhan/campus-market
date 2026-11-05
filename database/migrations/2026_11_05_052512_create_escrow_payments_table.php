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
        Schema::create('escrow_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('escrow_code')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('pending_deposit'); // pending_deposit, held, released, refunded, disputed
            $table->string('holding_channel'); // bca, mandiri, bni, bri, bsi, gopay, ovo, dana, shopeepay
            $table->string('holding_account_number');
            $table->string('holding_account_name');
            $table->string('deposit_proof')->nullable();
            $table->timestamp('deposited_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('seller_payout_details')->nullable();
            $table->json('buyer_refund_details')->nullable();
            $table->text('release_notes')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('use_escrow')->default(true)->after('payment_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('use_escrow');
        });

        Schema::dropIfExists('escrow_payments');
    }
};
