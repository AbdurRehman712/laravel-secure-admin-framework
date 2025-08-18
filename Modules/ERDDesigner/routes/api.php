<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ERDDesigner\app\Http\Controllers\Api\ERDApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (class_exists(ERDApiController::class)) {
    Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
        Route::apiResource('erd-projects', ERDApiController::class);
        Route::post('erd-projects/{project}/export-sql', [ERDApiController::class, 'exportSql']);
        Route::post('erd-projects/{project}/import-sql', [ERDApiController::class, 'importSql']);
        Route::post('erd-projects/{project}/export-module', [ERDApiController::class, 'exportToModuleBuilder']);
    });
}
