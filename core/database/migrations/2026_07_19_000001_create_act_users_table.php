<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('act_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('added_by'); // admin ID
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('added_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('act_users');
    }
};
