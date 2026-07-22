<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_bonus_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // matrices.id is int(11) signed — must use integer (not unsignedInteger) to match
            $table->integer('matrix_id');
            $table->foreign('matrix_id')->references('id')->on('matrices')->cascadeOnDelete();

            $table->unsignedBigInteger('project_id');   // audit: which project rate was applied
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();

            $table->unsignedInteger('matches');          // number of matches processed in this run
            $table->decimal('pv_per_match',    15, 2);  // MATCHING_NUMBER at time of processing
            $table->decimal('bonus_per_match', 15, 2);  // project pairing_per_day at time of processing
            $table->decimal('total_bonus',     15, 2);  // matches × bonus_per_match

            $table->decimal('pv_left_before',  15, 2);
            $table->decimal('pv_right_before', 15, 2);
            $table->decimal('pv_left_after',   15, 2);
            $table->decimal('pv_right_after',  15, 2);

            $table->string('trx', 40)->unique();         // links to transactions.trx

            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            // fast lookup: all matches for a user on a given day
            $table->index(['user_id', 'processed_at'], 'mbl_user_date');
            // admin reporting by date
            $table->index('processed_at', 'mbl_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_bonus_logs');
    }
};
