<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('module_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('controller_name')->nullable();
            $table->string('migration_name')->nullable();
            $table->string('resource_name')->nullable();
            $table->boolean('is_pivot')->default(false);
            $table->boolean('has_timestamps')->default(true);
            $table->boolean('has_soft_deletes')->default(false);
            $table->boolean('has_uuid')->default(false);
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_tables');
    }
};
