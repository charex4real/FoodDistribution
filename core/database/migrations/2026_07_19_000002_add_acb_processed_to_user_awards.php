<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_awards', function (Blueprint $table) {
            $table->boolean('acb_processed')->default(false)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('user_awards', function (Blueprint $table) {
            $table->dropColumn('acb_processed');
        });
    }
};
