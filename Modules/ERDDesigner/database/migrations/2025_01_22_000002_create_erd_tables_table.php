<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erd_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('erd_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('width')->default(200);
            $table->integer('height')->default(150);
            $table->string('color')->default('#ffffff');
            $table->string('engine')->default('InnoDB');
            $table->string('charset')->default('utf8mb4');
            $table->string('collation')->default('utf8mb4_unicode_ci');
            $table->text('comment')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['project_id', 'name']);
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erd_tables');
    }
};
