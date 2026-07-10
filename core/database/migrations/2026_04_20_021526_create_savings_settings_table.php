<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['target', 'fixed', 'farm']);
            $table->unsignedTinyInteger('duration_months')->nullable(); // fixed only: 3, 6, 12
            $table->string('label');
            $table->decimal('interest_rate', 5, 2)->default(8.00);     // % p.a.
            $table->decimal('min_amount', 15, 2)->default(100);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'duration_months']);
            $table->index('type');
        });

        $now = now();
        DB::table('savings_settings')->insert([
            ['type' => 'target', 'duration_months' => null, 'label' => 'Target Savings',        'interest_rate' => 8.00,  'min_amount' => 100,   'max_amount' => null, 'description' => 'Goal-based savings with weekly or monthly contributions.',      'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'fixed',  'duration_months' => 3,    'label' => 'Fixed Box — 3 Months',  'interest_rate' => 8.00,  'min_amount' => 500,   'max_amount' => null, 'description' => 'Lock funds for 3 months at a guaranteed interest rate.',        'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'fixed',  'duration_months' => 6,    'label' => 'Fixed Box — 6 Months',  'interest_rate' => 10.00, 'min_amount' => 500,   'max_amount' => null, 'description' => 'Lock funds for 6 months at a higher interest rate.',            'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'fixed',  'duration_months' => 12,   'label' => 'Fixed Box — 12 Months', 'interest_rate' => 14.00, 'min_amount' => 500,   'max_amount' => null, 'description' => 'Lock funds for 12 months for maximum interest reward.',         'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'farm',   'duration_months' => null, 'label' => 'Farm Yield Savings',    'interest_rate' => 12.00, 'min_amount' => 1000,  'max_amount' => null, 'description' => 'Participate in a farm cycle and earn yield-based returns.',     'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_settings');
    }
};
