<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('erd_tables')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['INDEX', 'UNIQUE', 'PRIMARY', 'FULLTEXT', 'SPATIAL'])->default('INDEX');
            $table->json('fields'); // Array of field names
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['table_id', 'name']);
            $table->index('table_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_indexes');
    }
};
