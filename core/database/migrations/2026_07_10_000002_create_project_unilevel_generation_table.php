<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_unilevel_generation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('generation_id')->constrained('unilevel_generations')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'generation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_unilevel_generation');
    }
};
