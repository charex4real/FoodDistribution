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
        Schema::create('dividend_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_id')->constrained('admins')->onDelete('restrict');
            $table->decimal('amount_per_unit', 15, 2);
            $table->enum('scope', ['all-time', 'date-range']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->integer('total_users')->default(0);
            $table->unsignedBigInteger('total_units')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('processed_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['created_by_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividend_batches');
    }
};
