<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('module_projects')->onDelete('cascade');
            $table->string('type'); // command, event, job, mail, etc.
            $table->string('name');
            $table->string('class_name');
            $table->text('description')->nullable();
            $table->string('namespace')->nullable();
            $table->string('file_path')->nullable();
            $table->string('template')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_components');
    }
};
