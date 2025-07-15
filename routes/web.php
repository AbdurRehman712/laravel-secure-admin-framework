<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route for project workspace - this will be handled by Filament automatically
