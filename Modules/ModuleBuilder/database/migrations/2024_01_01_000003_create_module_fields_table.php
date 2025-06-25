<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('module_tables')->onDelete('cascade');
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('type')->default('string');
            $table->string('database_type')->default('string');
            $table->integer('length')->nullable();
            $table->integer('precision')->nullable();
            $table->integer('scale')->nullable();
            $table->string('default_value')->nullable();
            $table->json('enum_values')->nullable();
            $table->boolean('nullable')->default(true);
            $table->boolean('unsigned')->default(false);
            $table->boolean('auto_increment')->default(false);
            $table->boolean('primary_key')->default(false);
            $table->boolean('unique')->default(false);
            $table->boolean('index')->default(false);
            $table->string('foreign_key_table')->nullable();
            $table->string('foreign_key_column')->nullable();
            $table->string('on_delete')->default('cascade');
            $table->string('on_update')->default('cascade');
            $table->json('validation_rules')->nullable();
            $table->string('filament_type')->default('text');
            $table->json('filament_options')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_fillable')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_searchable')->default(true);
            $table->boolean('is_sortable')->default(true);
            $table->boolean('is_filterable')->default(true);
            $table->string('cast_type')->nullable();
            $table->timestamps();

            $table->unique(['table_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_fields');
    }
};
