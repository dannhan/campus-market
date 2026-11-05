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
            $table->string('moderation_status')->default('approved')->after('status')->index();
            $table->boolean('is_verified')->default(false)->after('moderation_status')->index();
            $table->timestamp('moderated_at')->nullable()->after('is_verified');
            $table->foreignId('moderated_by_id')->nullable()->after('moderated_at')->constrained('users')->nullOnDelete();
            $table->text('moderation_notes')->nullable()->after('moderated_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['moderated_by_id']);
            $table->dropColumn(['moderation_status', 'is_verified', 'moderated_at', 'moderated_by_id', 'moderation_notes']);
        });
    }
};
