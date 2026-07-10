<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['image', 'video']);
            $table->string('file'); // stored filename
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('flashcard_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('flashcard_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at')->useCurrent();
            $table->unique(['user_id', 'flashcard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_views');
        Schema::dropIfExists('flashcards');
    }
};
