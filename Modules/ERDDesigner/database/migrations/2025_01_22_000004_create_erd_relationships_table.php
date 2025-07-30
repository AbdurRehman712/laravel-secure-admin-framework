<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('erd_projects')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->enum('type', ['one_to_one', 'one_to_many', 'many_to_one', 'many_to_many']);
            $table->foreignId('source_table_id')->constrained('erd_tables')->onDelete('cascade');
            $table->foreignId('target_table_id')->constrained('erd_tables')->onDelete('cascade');
            $table->foreignId('source_field_id')->nullable()->constrained('erd_fields')->onDelete('cascade');
            $table->foreignId('target_field_id')->nullable()->constrained('erd_fields')->onDelete('cascade');
            $table->enum('on_update', ['RESTRICT', 'CASCADE', 'SET NULL', 'NO ACTION', 'SET DEFAULT'])->default('RESTRICT');
            $table->enum('on_delete', ['RESTRICT', 'CASCADE', 'SET NULL', 'NO ACTION', 'SET DEFAULT'])->default('RESTRICT');
            $table->boolean('is_identifying')->default(false);
            $table->enum('cardinality_source', ['zero_or_one', 'exactly_one', 'zero_or_many', 'one_or_many'])->default('exactly_one');
            $table->enum('cardinality_target', ['zero_or_one', 'exactly_one', 'zero_or_many', 'one_or_many'])->default('zero_or_many');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index(['source_table_id', 'target_table_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_relationships');
    }
};
