<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('database_name')->nullable();
            $table->string('database_type')->default('mysql');
            $table->json('canvas_data')->nullable();
            $table->json('settings')->nullable();
            $table->string('version')->default('1.0.0');
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['created_by', 'is_public']);
            $table->index('database_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_projects');
    }
};
