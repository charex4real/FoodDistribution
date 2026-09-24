<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_change_logs', function (Blueprint $table) {
            $table->id();

            // The downline whose sponsor (users.ref_by) was changed
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Sponsor before the change — kept nullable so history survives a sponsor account being deleted
            $table->unsignedBigInteger('previous_sponsor_id')->nullable();
            $table->foreign('previous_sponsor_id')->references('id')->on('users')->nullOnDelete();

            // Sponsor after the change
            $table->unsignedBigInteger('new_sponsor_id')->nullable();
            $table->foreign('new_sponsor_id')->references('id')->on('users')->nullOnDelete();

            // Admin who made the change
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_change_logs');
    }
};
