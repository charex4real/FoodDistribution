<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('withdrawal_blocked')->default(false)->after('ban_reason');
            $table->text('withdrawal_block_reason')->nullable()->after('withdrawal_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['withdrawal_blocked', 'withdrawal_block_reason']);
        });
    }
};
