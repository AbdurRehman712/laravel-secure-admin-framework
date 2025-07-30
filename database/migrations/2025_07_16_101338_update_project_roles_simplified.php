<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum to include new roles
        DB::statement("ALTER TABLE project_team_members MODIFY COLUMN role ENUM('product_owner', 'designer', 'database_admin', 'frontend_developer', 'backend_developer', 'devops', 'database_backend_developer', 'project_manager')");

        // Update existing roles to new simplified structure
        DB::table('project_team_members')
            ->where('role', 'designer')
            ->update(['role' => 'project_manager']);

        DB::table('project_team_members')
            ->where('role', 'frontend_developer')
            ->update(['role' => 'project_manager']);

        DB::table('project_team_members')
            ->where('role', 'devops')
            ->update(['role' => 'project_manager']);

        DB::table('project_team_members')
            ->where('role', 'database_admin')
            ->update(['role' => 'database_backend_developer']);

        DB::table('project_team_members')
            ->where('role', 'backend_developer')
            ->update(['role' => 'database_backend_developer']);

        // Update workspace content roles
        DB::table('project_workspace_contents')
            ->where('role', 'designer')
            ->update(['role' => 'project_manager']);

        DB::table('project_workspace_contents')
            ->where('role', 'frontend_developer')
            ->update(['role' => 'project_manager']);

        DB::table('project_workspace_contents')
            ->where('role', 'devops')
            ->update(['role' => 'project_manager']);

        DB::table('project_workspace_contents')
            ->where('role', 'database_admin')
            ->update(['role' => 'database_backend_developer']);

        DB::table('project_workspace_contents')
            ->where('role', 'backend_developer')
            ->update(['role' => 'database_backend_developer']);

        // Finally, restrict enum to only new roles
        DB::statement("ALTER TABLE project_team_members MODIFY COLUMN role ENUM('product_owner', 'database_backend_developer', 'project_manager')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the role changes
        DB::table('project_team_members')
            ->where('role', 'project_manager')
            ->update(['role' => 'devops']);

        DB::table('project_team_members')
            ->where('role', 'database_backend_developer')
            ->update(['role' => 'database_admin']);

        DB::table('project_workspace_contents')
            ->where('role', 'project_manager')
            ->update(['role' => 'devops']);

        DB::table('project_workspace_contents')
            ->where('role', 'database_backend_developer')
            ->update(['role' => 'database_admin']);
    }
};
