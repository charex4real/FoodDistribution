<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stockist_redemptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('stockist_id');
            $table->foreign('stockist_id')->references('id')->on('stockists')->cascadeOnDelete();

            $table->string('type', 30); // cash|invoice_code|welcome_pack|affiliate_invoice

            $table->string('trx', 64)->nullable();
            $table->string('reference_code', 64)->nullable();

            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();

            $table->unsignedBigInteger('welcome_package_id')->nullable();
            $table->foreign('welcome_package_id')->references('id')->on('welcome_packages')->nullOnDelete();

            $table->unsignedBigInteger('affiliate_order_id')->nullable();
            $table->foreign('affiliate_order_id')->references('id')->on('affiliate_orders')->nullOnDelete();

            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->foreign('customer_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('buyer_name', 191)->nullable();

            $table->json('items')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('amount', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('redeemed_at')->nullable();

            $table->timestamps();

            $table->index(['stockist_id', 'type']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stockist_redemptions');
    }
};
