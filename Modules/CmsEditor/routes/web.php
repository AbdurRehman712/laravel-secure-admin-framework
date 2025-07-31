<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\CmsEditor\app\Http\Controllers\CmsPageController;
use Modules\CmsEditor\app\Http\Controllers\ThemeController;
use Modules\CmsEditor\app\Http\Controllers\ThemePageController;

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

// Test route for debugging
Route::get('cmseditor/test', function() {
    return 'CMS Editor is working!';
})->name('cmseditor.test');

// Public routes for CMS pages
Route::prefix('page')->name('cmseditor.pages.')->group(function () {
    Route::get('{slug?}', [CmsPageController::class, 'showPage'])->name('show');
});

// Admin routes (requires authentication)
Route::middleware(['auth:admin'])->prefix('admin')->name('cmseditor.admin.')->group(function () {
    // Preview routes
    Route::get('preview/{page}', [CmsPageController::class, 'previewPage'])->name('preview');
    
    // Theme management routes
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->name('index');
        Route::get('{theme}/structure', [ThemeController::class, 'getThemeStructure'])->name('structure');
        Route::get('{theme}/file/{file?}', [ThemeController::class, 'getFile'])->name('file.get')->where('file', '.*');
        Route::post('{theme}/file', [ThemeController::class, 'saveFile'])->name('file.save');
        Route::post('{theme}/file/create', [ThemeController::class, 'createFile'])->name('file.create');
        Route::delete('{theme}/file', [ThemeController::class, 'deleteFile'])->name('file.delete');
    });
});

// Named routes for common pages
Route::get('login', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/login');
})->name('login');
Route::get('register', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/register');
})->name('register');
Route::get('dashboard', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/dashboard');
})->name('dashboard');
Route::get('profile', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/profile');
})->name('profile');
Route::get('settings', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/settings');
})->name('settings');
Route::get('forgot-password', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/forgot-password');
})->name('password.request');
Route::get('reset-password', function(Request $request) {
    return app(ThemePageController::class)->showPage($request, '/reset-password');
})->name('password.reset');

// Authentication routes (POST)
Route::post('login', [ThemePageController::class, 'handleLogin'])->name('login.post');
Route::post('register', [ThemePageController::class, 'handleRegister'])->name('register.post');
Route::post('logout', [ThemePageController::class, 'handleLogout'])->name('logout');
Route::post('forgot-password', [ThemePageController::class, 'handleForgotPassword'])->name('password.email');
Route::post('reset-password', [ThemePageController::class, 'handleResetPassword'])->name('password.update');

// Settings and profile routes (POST and PUT)
Route::post('profile', [ThemePageController::class, 'handleProfileUpdate'])->name('profile.update');
Route::put('profile', [ThemePageController::class, 'handleProfileUpdate'])->name('profile.put');
Route::post('settings', [ThemePageController::class, 'handleSettingsUpdate'])->name('settings.update');

// Theme-based pages (catch-all route - should be last)
Route::get('{url?}', [ThemePageController::class, 'showPage'])
    ->where('url', '.*')
    ->name('theme.page.show');
