<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleBuilder\Http\Controllers\ModuleBuilderController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('modulebuilders', ModuleBuilderController::class)->names('modulebuilder');
});
