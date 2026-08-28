<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('affiliate_bonus_type', 20)->nullable()->after('bv'); // 'fixed' | 'percentage'
            $table->decimal('affiliate_bonus_value', 15, 2)->nullable()->after('affiliate_bonus_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['affiliate_bonus_type', 'affiliate_bonus_value']);
        });
    }
};
