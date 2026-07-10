<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_product_id')->constrained('loan_products')->onDelete('restrict');

            $table->string('reference')->unique();    // e.g. LN-2026-00001

            // ── Financials ────────────────────────────────────────────
            $table->decimal('original_amount', 15, 2);               // disbursed principal
            $table->decimal('interest_rate', 5, 2);                  // % p.a. at time of approval
            $table->decimal('total_interest', 15, 2)->default(0);    // pre-calculated total interest
            $table->decimal('total_repayable', 15, 2)->default(0);   // original + total_interest
            $table->decimal('outstanding', 15, 2)->default(0);        // remaining balance
            $table->decimal('total_repaid', 15, 2)->default(0);

            // ── Installment info ──────────────────────────────────────
            $table->unsignedSmallInteger('tenure_months');
            $table->decimal('monthly_installment', 15, 2)->default(0);
            $table->date('next_due_date')->nullable();
            $table->unsignedSmallInteger('installments_paid')->default(0);
            $table->unsignedSmallInteger('installments_overdue')->default(0);

            // ── Collateral snapshot ───────────────────────────────────
            // Savings Wallet balance at time of application (for audit)
            $table->decimal('savings_balance_at_application', 15, 2)->default(0);

            // ── Repayment priority flag ───────────────────────────────
            // When true, 100% of earnings (after 10% savings) go to repayment
            $table->boolean('repayment_priority')->default(false);

            // ── Status flow ───────────────────────────────────────────
            // pending → approved → active → at_risk → defaulted → cleared
            // or pending → rejected
            $table->enum('status', [
                'pending', 'approved', 'active',
                'at_risk', 'defaulted', 'cleared', 'rejected'
            ])->default('pending');

            // ── Timestamps ────────────────────────────────────────────
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamp('defaulted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();    // admin id

            $table->string('purpose')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_due_date']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
