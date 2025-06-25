<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('module_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('guard_name')->default('admin');
            $table->text('description')->nullable();
            $table->string('group_name')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_permissions');
    }
};
