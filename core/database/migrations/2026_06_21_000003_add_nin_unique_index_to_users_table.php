<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Enforce NIN uniqueness at the DB level — the application layer
            // already validates this, but the index is the final safety net
            // against any race condition that bypasses the validation check.
            $table->unique('nin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nin']);
        });
    }
};
