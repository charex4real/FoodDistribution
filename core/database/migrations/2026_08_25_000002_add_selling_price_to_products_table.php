<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'selling_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('selling_price', 28, 8)->nullable()->after('price');
            });
        }

        // Backfill so /shop never shows a blank/zero price for existing catalog
        // products before an admin explicitly sets one.
        DB::table('products')->whereNull('selling_price')->update(['selling_price' => DB::raw('price')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'selling_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('selling_price');
            });
        }
    }
};
