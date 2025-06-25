<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('module_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('namespace');
            $table->text('description')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('homepage')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('icon')->nullable();
            $table->enum('status', ['draft', 'building', 'built', 'error'])->default('draft');
            $table->boolean('enabled')->default(false);
            $table->boolean('has_api')->default(false);
            $table->boolean('has_web_routes')->default(false);
            $table->boolean('has_admin_panel')->default(true);
            $table->boolean('has_frontend')->default(false);
            $table->boolean('has_permissions')->default(true);
            $table->boolean('has_middleware')->default(false);
            $table->boolean('has_commands')->default(false);
            $table->boolean('has_events')->default(false);
            $table->boolean('has_jobs')->default(false);
            $table->boolean('has_mail')->default(false);
            $table->boolean('has_notifications')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_projects');
    }
};
