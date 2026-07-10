<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nin', 20)->nullable()->after('kv');
            $table->string('id_card_type', 50)->nullable()->after('nin');
            $table->string('id_card_image')->nullable()->after('id_card_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nin', 'id_card_type', 'id_card_image']);
        });
    }
};
