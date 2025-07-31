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
        Schema::create('theme_pages', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name');
            $table->string('page_name');
            $table->string('file_path');
            $table->string('title');
            $table->string('url')->unique();
            $table->string('layout')->default('default');
            $table->text('description')->nullable();
            $table->boolean('hidden')->default(false);
            $table->json('meta_data')->nullable(); // For additional metadata
            $table->timestamps();

            $table->unique(['theme_name', 'page_name']);
            $table->index(['theme_name', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_pages');
    }
};
