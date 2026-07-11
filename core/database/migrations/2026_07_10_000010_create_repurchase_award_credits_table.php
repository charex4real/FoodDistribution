<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repurchase_award_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repurchase_award_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('paid_by');   // admin id
            $table->timestamp('paid_at');
            $table->unique(['user_id', 'repurchase_award_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repurchase_award_credits');
    }
};
