<?php

use Illuminate\Support\Facades\Route;
use Modules\PublicUser\app\Http\Controllers\Auth\LoginController;
use Modules\PublicUser\app\Http\Controllers\PublicUserController;

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

Route::prefix('user')->name('publicuser.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    // Authenticated routes
    Route::middleware(['auth:web'])->group(function () {
        Route::get('dashboard', [PublicUserController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    });
});
