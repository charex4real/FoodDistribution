<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Locked savings — auto-funded at 10% of every earning, never directly withdrawable
            $table->decimal('savings_wallet', 15, 2)->default(0)->after('balance');
            // Note: liquid balance uses the existing $user->balance column (no money_box column needed)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['savings_wallet']);
        });
    }
};
