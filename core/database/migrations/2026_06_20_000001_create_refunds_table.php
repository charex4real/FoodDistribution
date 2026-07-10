<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_id'); // 1, 2, or 3

            // The specific original duplicate transaction that triggered the refund
            $table->string('original_trx');

            // The new deduction transaction created for this refund
            $table->string('refund_trx')->unique();

            $table->decimal('amount', 15, 2);

            // Balance snapshot
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            // Who performed the refund
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            // One refund per user per stage — enforced at DB level
            $table->unique(['user_id', 'stage_id']);

            $table->index(['stage_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
