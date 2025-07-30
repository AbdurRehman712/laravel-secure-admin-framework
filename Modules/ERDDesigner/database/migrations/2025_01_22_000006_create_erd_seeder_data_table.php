<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_seeder_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('erd_tables')->onDelete('cascade');
            $table->string('field_name');
            $table->json('sample_data')->nullable();
            $table->enum('data_type', ['static', 'faker', 'sequence', 'random', 'formula'])->default('static');
            $table->text('generation_rule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['table_id', 'field_name']);
            $table->index('table_id');
            $table->index('data_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_seeder_data');
    }
};
