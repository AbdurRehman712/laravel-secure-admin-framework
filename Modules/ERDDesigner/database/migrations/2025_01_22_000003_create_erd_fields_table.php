<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('erd_tables')->onDelete('cascade');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('type');
            $table->integer('length')->nullable();
            $table->integer('precision')->nullable();
            $table->integer('scale')->nullable();
            $table->boolean('is_nullable')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_auto_increment')->default(false);
            $table->boolean('is_foreign_key')->default(false);
            $table->string('default_value')->nullable();
            $table->text('comment')->nullable();
            $table->integer('order')->default(0);
            $table->text('validation_rules')->nullable();
            $table->string('form_type')->default('text');
            $table->json('form_options')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['table_id', 'name']);
            $table->index(['table_id', 'order']);
            $table->index(['is_primary', 'is_foreign_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_fields');
    }
};
