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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['planning', 'development', 'review', 'completed', 'archived'])
                  ->default('planning');
            $table->foreignId('created_by')->constrained('admins')->onDelete('cascade');
            $table->json('settings')->nullable();
            $table->json('ai_context')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_by']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
