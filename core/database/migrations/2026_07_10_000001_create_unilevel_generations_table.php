<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unilevel_generations', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique()->comment('Generation level 1–15');
            $table->string('title', 100);
            $table->decimal('percentage', 8, 4)->default(0)->comment('% of PRB allocated to this generation');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unilevel_generations');
    }
};
