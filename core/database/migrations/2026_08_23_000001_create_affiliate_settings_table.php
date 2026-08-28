<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('cash_on_pickup_enabled')->default(true);
            $table->boolean('paystack_enabled')->default(true);
            $table->unsignedInteger('cookie_days')->default(30);
            $table->string('stockist_pickup_fee_type', 20)->nullable(); // 'fixed' | 'percentage'
            $table->decimal('stockist_pickup_fee_value', 15, 2)->default(0);
            $table->unsignedInteger('pending_order_expiry_days')->default(7);
            $table->timestamps();
        });

        // Seed the single settings row this feature always reads/writes.
        DB::table('affiliate_settings')->insert([
            'cash_on_pickup_enabled' => true,
            'paystack_enabled'       => true,
            'cookie_days'            => 30,
            'stockist_pickup_fee_type'  => 'fixed',
            'stockist_pickup_fee_value' => 0,
            'pending_order_expiry_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_settings');
    }
};
