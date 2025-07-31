<?php

use Illuminate\Support\Facades\Route;
use Modules\CmsEditor\app\Http\Controllers\CmsEditorController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('cmseditors', CmsEditorController::class)->names('cmseditor');
});
