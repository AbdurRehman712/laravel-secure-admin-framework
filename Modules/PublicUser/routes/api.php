<?php

use Illuminate\Support\Facades\Route;
use Modules\PublicUser\Http\Controllers\PublicUserController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('publicusers', PublicUserController::class)->names('publicuser');
});
