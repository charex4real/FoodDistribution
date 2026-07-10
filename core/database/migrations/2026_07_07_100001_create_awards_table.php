<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->decimal('required_total_pv', 12, 2)->default(0);
            $table->decimal('required_left_pv', 12, 2)->default(0);
            $table->decimal('required_right_pv', 12, 2)->default(0);
            $table->unsignedBigInteger('prerequisite_award_id')->nullable();
            $table->foreign('prerequisite_award_id')->references('id')->on('awards')->nullOnDelete();
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
