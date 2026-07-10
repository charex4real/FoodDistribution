<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // target | fixed | farm
            $table->enum('type', ['target', 'fixed', 'farm']);
            $table->string('name');                                  // user-defined label
            $table->string('reference')->unique();                   // e.g. SV-2026-00001

            // ── Balances ──────────────────────────────────────────────
            $table->decimal('principal', 15, 2)->default(0);        // total deposited
            $table->decimal('balance', 15, 2)->default(0);          // current balance (principal + added funds)
            $table->decimal('target_amount', 15, 2)->nullable();    // goal for Target type
            $table->decimal('interest_rate', 5, 2)->default(0);     // % p.a.
            $table->decimal('interest_earned', 15, 2)->default(0);  // accumulated/projected

            // ── Schedule (Target Savings) ─────────────────────────────
            $table->enum('frequency', ['weekly', 'monthly'])->nullable();
            $table->unsignedSmallInteger('duration_cycles')->nullable(); // e.g. 12 weeks
            $table->decimal('contribution_per_cycle', 15, 2)->nullable();
            $table->unsignedSmallInteger('cycles_completed')->default(0);
            $table->date('next_due_date')->nullable();

            // ── Dates ─────────────────────────────────────────────────
            $table->date('start_date');
            $table->date('maturity_date');
            $table->timestamp('matured_at')->nullable();             // system-set when maturity reached
            $table->timestamp('closed_at')->nullable();              // set after withdrawal

            // ── Farm Yield link ───────────────────────────────────────
            $table->foreignId('farm_cycle_id')
                  ->nullable()
                  ->constrained('farm_cycles')
                  ->onDelete('restrict');

            // ── Status ───────────────────────────────────────────────
            // active | matured | closed | pending
            $table->enum('status', ['active', 'matured', 'closed', 'pending'])->default('active');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'maturity_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_products');
    }
};
