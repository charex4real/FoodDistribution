<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 15, 2);
            $table->string('reference')->unique();

            // manual (user-initiated from Money Box) | automatic (deducted from earnings)
            $table->enum('source', ['manual', 'automatic']);

            // Snapshot for audit
            $table->decimal('outstanding_before', 15, 2)->default(0);
            $table->decimal('outstanding_after', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->default(0);   // $user->balance snapshot
            $table->decimal('balance_after', 15, 2)->default(0);

            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');

            // Link to earning event that triggered auto-repayment (nullable for manual)
            $table->string('earning_reference')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('source');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
