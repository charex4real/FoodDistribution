<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repurchase_awards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('required_pv', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repurchase_awards');
    }
};
