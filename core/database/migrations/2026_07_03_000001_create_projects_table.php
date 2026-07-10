<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('las la-briefcase');
            $table->string('color', 20)->default('#059669');
            // Financials
            $table->decimal('amount', 15, 2)->default(0)->comment('Activation cost / project price');
            $table->decimal('direct_commission', 5, 2)->default(0)->comment('Direct referral commission %');
            $table->decimal('indirect_commission', 5, 2)->default(0)->comment('Indirect / binary spill-over commission %');
            $table->decimal('pv', 10, 2)->default(0)->comment('Point Value awarded on activation');
            $table->decimal('pairing_per_day', 15, 2)->default(0)->comment('Max pairing bonus paid per day (0 = unlimited)');
            $table->decimal('cash_back', 5, 2)->default(0)->comment('% cash-back on activation');
            $table->decimal('monthly_maintenance', 15, 2)->default(0)->comment('Monthly maintenance fee (0 = none)');
            $table->decimal('upgrade_bonus', 5, 2)->default(0)->comment('% bonus paid to sponsor when member upgrades to this project');
            $table->decimal('unilevel_bonus', 5, 2)->default(0)->comment('% unilevel pool bonus');
            // Limits
            $table->unsignedInteger('max_pairing_slots')->default(0)->comment('0 = unlimited');
            $table->boolean('upgrade_allowed')->default(true)->comment('Can members upgrade into this project');
            $table->boolean('is_default')->default(false)->comment('Pre-selected during registration');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('status')->default(true)->comment('1 = active');
            $table->timestamps();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
