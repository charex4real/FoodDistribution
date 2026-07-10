<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farm_cycles', function (Blueprint $table) {
            $table->timestamp('matured_at')->nullable()->after('maturity_date');
            $table->boolean('payout_processed')->default(false)->after('matured_at');
        });
    }

    public function down(): void
    {
        Schema::table('farm_cycles', function (Blueprint $table) {
            $table->dropColumn(['matured_at', 'payout_processed']);
        });
    }
};
