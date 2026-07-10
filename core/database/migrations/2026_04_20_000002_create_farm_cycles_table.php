<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // open = accepting subscriptions, closed = no new subs, matured = cycle completed
            $table->enum('status', ['open', 'closed', 'matured'])->default('open');
            $table->decimal('min_contribution', 15, 2)->default(1000);
            $table->decimal('max_contribution', 15, 2)->nullable();
            $table->decimal('min_yield', 5, 2)->default(0);   // % return lower bound
            $table->decimal('max_yield', 5, 2)->default(0);   // % return upper bound
            $table->decimal('actual_yield', 5, 2)->nullable(); // set when cycle matures
            $table->date('subscription_opens_at');
            $table->date('subscription_closes_at');
            $table->date('maturity_date');
            $table->unsignedBigInteger('created_by')->nullable(); // admin id
            $table->timestamps();

            $table->index('status');
            $table->index('maturity_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_cycles');
    }
};
