<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_user_id');
            $table->foreign('affiliate_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('session_token', 64); // value stored in the aff_ref attribution cookie
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('landing_url', 500)->nullable();
            $table->timestamps();

            $table->index('affiliate_user_id');
            $table->index('session_token');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
