<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Eligibility rules
            $table->unsignedInteger('min_membership_days')->default(0);   // days since registration
            $table->decimal('min_savings_threshold', 15, 2)->default(100); // min savings_wallet to qualify

            // Loan sizing
            $table->decimal('savings_multiple', 5, 2)->default(2.00);     // max loan = savings × multiple
            $table->decimal('max_loan_amount', 15, 2)->nullable();         // hard cap (optional)
            $table->decimal('min_loan_amount', 15, 2)->default(500);

            // Cost of credit
            $table->decimal('interest_rate', 5, 2)->default(5.00);        // % per annum
            $table->json('tenure_options');                                 // e.g. [3, 6, 12] months

            // Operational
            $table->boolean('auto_approve')->default(false);               // skip admin review
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();          // admin id
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
