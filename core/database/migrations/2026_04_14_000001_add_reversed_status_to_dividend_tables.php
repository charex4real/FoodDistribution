<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify status enum on dividend_batches
        DB::statement("ALTER TABLE dividend_batches MODIFY COLUMN status ENUM('pending','processing','completed','cancelled','failed','reversed') NOT NULL DEFAULT 'pending'");

        // Add reversed_at column if it doesn't exist
        if (!Schema::hasColumn('dividend_batches', 'reversed_at')) {
            Schema::table('dividend_batches', function (Blueprint $table) {
                $table->timestamp('reversed_at')->nullable()->after('cancelled_at');
            });
        }

        // Add reversed_by_id column if it doesn't exist
        if (!Schema::hasColumn('dividend_batches', 'reversed_by_id')) {
            Schema::table('dividend_batches', function (Blueprint $table) {
                $table->unsignedBigInteger('reversed_by_id')->nullable()->after('reversed_at');
            });
        }

        // Modify status enum on shtransactions
        DB::statement("ALTER TABLE shtransactions MODIFY COLUMN status ENUM('pending','completed','failed','reversed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (Schema::hasColumn('dividend_batches', 'reversed_at')) {
            Schema::table('dividend_batches', function (Blueprint $table) {
                $table->dropColumn('reversed_at');
            });
        }

        if (Schema::hasColumn('dividend_batches', 'reversed_by_id')) {
            Schema::table('dividend_batches', function (Blueprint $table) {
                $table->dropColumn('reversed_by_id');
            });
        }

        DB::statement("ALTER TABLE dividend_batches MODIFY COLUMN status ENUM('pending','processing','completed','cancelled','failed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE shtransactions MODIFY COLUMN status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending'");
    }
};
