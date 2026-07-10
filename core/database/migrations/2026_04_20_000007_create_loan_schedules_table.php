<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->unsignedSmallInteger('installment_number');  // 1, 2, 3…
            $table->date('due_date');
            $table->decimal('principal', 15, 2);
            $table->decimal('interest', 15, 2);
            $table->decimal('total', 15, 2);                     // principal + interest

            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);  // total - amount_paid

            // pending | paid | partial | overdue | defaulted
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'defaulted'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['loan_id', 'due_date']);
            $table->index(['loan_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
