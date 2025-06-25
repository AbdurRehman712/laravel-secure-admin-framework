<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_table_id')->constrained('module_tables')->onDelete('cascade');
            $table->foreignId('to_table_id')->constrained('module_tables')->onDelete('cascade');
            $table->string('type'); // hasOne, hasMany, belongsTo, belongsToMany, etc.
            $table->string('name');
            $table->string('inverse_name')->nullable();
            $table->string('foreign_key')->nullable();
            $table->string('local_key')->nullable();
            $table->string('pivot_table')->nullable();
            $table->string('pivot_foreign_key')->nullable();
            $table->string('pivot_related_key')->nullable();
            $table->string('morph_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_relationships');
    }
};
