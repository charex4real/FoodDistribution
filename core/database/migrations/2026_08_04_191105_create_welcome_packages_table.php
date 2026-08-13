<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('welcome_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('code', 12)->unique();               // human-presentable redemption code
            $table->string('source', 20);                       // 'registration' | 'upgrade'
            $table->string('status', 20)->default('pending');   // 'pending' | 'redeemed'
            $table->string('trx', 40)->unique();                // internal ledger reference
            $table->unsignedBigInteger('redeemed_by_stockist_id')->nullable();
            $table->foreign('redeemed_by_stockist_id')->references('id')->on('stockists')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_packages');
    }
};
