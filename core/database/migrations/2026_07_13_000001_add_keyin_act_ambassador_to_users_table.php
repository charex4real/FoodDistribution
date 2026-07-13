<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('key_in_bonus', 15, 2)->default(0)->after('repurchase_award');
            $table->decimal('acb', 15, 2)->default(0)->after('key_in_bonus');
            $table->boolean('ambassador')->default(false)->after('acb');
            $table->unsignedBigInteger('keyed_in_by')->nullable()->after('ambassador');
            $table->foreign('keyed_in_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('repurchase_award_credits', function (Blueprint $table) {
            $table->boolean('acb_processed')->default(false)->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['keyed_in_by']);
            $table->dropColumn(['key_in_bonus', 'acb', 'ambassador', 'keyed_in_by']);
        });

        Schema::table('repurchase_award_credits', function (Blueprint $table) {
            $table->dropColumn('acb_processed');
        });
    }
};
