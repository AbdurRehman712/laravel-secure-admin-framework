<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50);
            $table->string('customer_name', 255);
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->json('billing_address');
            $table->json('shipping_address')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('shipping_amount', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded']);
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('shipped_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};