<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
$table->id();
            $table->bigInteger('category_id');
            $table->text('description')->nullable();
            $table->string('name', 255);
            $table->decimal('price', 10, 2);
            $table->string('sku', 100)->nullable()->unique();
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->integer('stock_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};