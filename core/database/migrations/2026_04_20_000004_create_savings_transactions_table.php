<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Which wallet or product was affected
            // wallet_type: savings_wallet | balance | savings_product
            // balance = the main $user->balance (liquid Money Box equivalent)
            $table->enum('wallet_type', ['savings_wallet', 'balance', 'savings_product']);
            $table->foreignId('savings_product_id')
                  ->nullable()
                  ->constrained('savings_products')
                  ->onDelete('cascade');

            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);

            // What caused this transaction
            // earning_split | product_creation | add_funds | maturity_withdrawal
            // loan_disbursement | loan_auto_repayment | loan_manual_repayment | interest_credit
            $table->string('source');

            $table->string('description');
            $table->string('reference')->unique();

            // Running balance snapshot for audit
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);

            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();  // e.g. earning event id, loan id
            $table->timestamps();

            $table->index(['user_id', 'wallet_type', 'created_at']);
            $table->index(['savings_product_id', 'created_at']);
            $table->index('source');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
