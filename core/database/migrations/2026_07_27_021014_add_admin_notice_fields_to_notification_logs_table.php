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
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->boolean('is_admin_notice')->default(false)->after('user_read');
            $table->unsignedBigInteger('sent_by_admin_id')->nullable()->after('is_admin_notice');

            $table->index('is_admin_notice');
            $table->index(['user_id', 'user_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['is_admin_notice']);
            $table->dropIndex(['user_id', 'user_read']);
            $table->dropColumn(['is_admin_notice', 'sent_by_admin_id']);
        });
    }
};
