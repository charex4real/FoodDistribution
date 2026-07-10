<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // These were decimal(5,2) when they were percentages (max 999.99).
            // Now they store flat amounts, so widen to decimal(15,2).
            $table->decimal('direct_commission',   15, 2)->default(0)->change();
            $table->decimal('indirect_commission', 15, 2)->default(0)->change();
            $table->decimal('cash_back',           15, 2)->default(0)->change();
            $table->decimal('unilevel_bonus',      15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('direct_commission',   5, 2)->default(0)->change();
            $table->decimal('indirect_commission', 5, 2)->default(0)->change();
            $table->decimal('cash_back',           5, 2)->default(0)->change();
            $table->decimal('unilevel_bonus',      5, 2)->default(0)->change();
        });
    }
};
