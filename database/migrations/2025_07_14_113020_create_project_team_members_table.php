<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->enum('role', [
                'product_owner',
                'designer',
                'database_admin',
                'frontend_developer',
                'backend_developer',
                'devops'
            ]);
            $table->json('permissions')->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['project_id', 'admin_id']);
            $table->index(['project_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_team_members');
    }
};
