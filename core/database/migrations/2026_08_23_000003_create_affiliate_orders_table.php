<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique(); // doubles as the redemption code shown/emailed to the buyer

            $table->unsignedBigInteger('affiliate_user_id')->nullable();
            $table->foreign('affiliate_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unsignedBigInteger('affiliate_click_id')->nullable();
            $table->foreign('affiliate_click_id')->references('id')->on('affiliate_clicks')->nullOnDelete();

            $table->string('buyer_name', 191);
            $table->string('buyer_email', 191);
            $table->string('buyer_phone', 30)->nullable();

            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states')->restrictOnDelete();

            $table->string('payment_method', 20); // 'paystack' | 'cash_on_pickup'
            $table->string('status', 20)->default('pending'); // pending|paid|awaiting_pickup|fulfilled|cancelled|expired

            $table->decimal('subtotal', 15, 2);
            $table->decimal('total_amount', 15, 2);

            $table->string('paystack_reference', 100)->nullable()->index();
            $table->boolean('bonus_credited')->default(false);

            $table->unsignedBigInteger('redeemed_by_stockist_id')->nullable();
            $table->foreign('redeemed_by_stockist_id')->references('id')->on('stockists')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['affiliate_user_id', 'status']);
            $table->index('status');
            $table->index('state_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_orders');
    }
};
