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
        Schema::create('shtransactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rinvestment_id')->constrained()->onDelete('cascade');
            $table->foreignId('dividend_batch_id')->constrained('dividend_batches')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_per_unit', 15, 2);
            $table->integer('units_held');
            $table->string('description');
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['dividend_batch_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shtransactions');
    }
};
