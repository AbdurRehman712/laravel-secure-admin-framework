<?php

use Illuminate\Support\Facades\Route;
use Modules\ERDDesigner\app\Http\Controllers\ERDDesignerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::prefix('erd-designer')->name('erd-designer.')->group(function () {
        Route::get('/', [ERDDesignerController::class, 'index'])->name('index');
        Route::post('/projects', [ERDDesignerController::class, 'createProject'])->name('projects.create');
        Route::get('/projects/{project}', [ERDDesignerController::class, 'showProject'])->name('projects.show');
        Route::put('/projects/{project}', [ERDDesignerController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [ERDDesignerController::class, 'deleteProject'])->name('projects.delete');
        
        Route::post('/projects/{project}/export-sql', [ERDDesignerController::class, 'exportSql'])->name('projects.export-sql');
        Route::post('/projects/{project}/import-sql', [ERDDesignerController::class, 'importSql'])->name('projects.import-sql');
        Route::post('/projects/{project}/export-module', [ERDDesignerController::class, 'exportToModuleBuilder'])->name('projects.export-module');
        
        Route::post('/projects/{project}/tables', [ERDDesignerController::class, 'createTable'])->name('tables.create');
        Route::put('/tables/{table}', [ERDDesignerController::class, 'updateTable'])->name('tables.update');
        Route::delete('/tables/{table}', [ERDDesignerController::class, 'deleteTable'])->name('tables.delete');
        
        Route::post('/projects/{project}/relationships', [ERDDesignerController::class, 'createRelationship'])->name('relationships.create');
        Route::put('/relationships/{relationship}', [ERDDesignerController::class, 'updateRelationship'])->name('relationships.update');
        Route::delete('/relationships/{relationship}', [ERDDesignerController::class, 'deleteRelationship'])->name('relationships.delete');
    });
});
