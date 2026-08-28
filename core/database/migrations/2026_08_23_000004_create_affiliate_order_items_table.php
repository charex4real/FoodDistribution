<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('affiliate_order_id');
            $table->foreign('affiliate_order_id')->references('id')->on('affiliate_orders')->cascadeOnDelete();

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();

            $table->string('product_name', 191); // snapshot at time of sale
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);   // snapshot
            $table->decimal('line_total', 15, 2);
            $table->decimal('bonus_amount', 15, 2)->default(0); // snapshot of the affiliate bonus this line generated

            $table->timestamps();

            $table->index('affiliate_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_order_items');
    }
};
