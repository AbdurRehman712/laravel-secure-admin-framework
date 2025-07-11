<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku', 100);
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions', 100)->nullable();
            $table->string('featured_image')->nullable();
            $table->string('gallery')->nullable();
            $table->enum('status', ['draft', 'published', 'archived']);
            $table->boolean('featured')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('category_id')->constrained('shop_categories');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_products');
    }
};