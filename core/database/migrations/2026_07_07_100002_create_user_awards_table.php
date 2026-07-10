<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('award_id')->constrained('awards')->cascadeOnDelete();
            $table->tinyInteger('status')->default(0)->comment('0=earned/unpaid,1=paid');
            $table->timestamp('earned_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'award_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_awards');
    }
};
