<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matching_bonus_logs', function (Blueprint $table) {
            $table->boolean('autoship_fulfilled')->default(true)->after('total_bonus');
            $table->decimal('autoship_amount', 15, 2)->default(0)->after('autoship_fulfilled');
        });
    }

    public function down(): void
    {
        Schema::table('matching_bonus_logs', function (Blueprint $table) {
            $table->dropColumn(['autoship_fulfilled', 'autoship_amount']);
        });
    }
};
