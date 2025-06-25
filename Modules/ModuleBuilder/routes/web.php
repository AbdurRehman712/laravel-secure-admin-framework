<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleBuilder\Http\Controllers\ModuleBuilderController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('modulebuilders', ModuleBuilderController::class)->names('modulebuilder');
});
