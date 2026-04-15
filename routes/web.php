<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return to_route('filament.admin.auth.login');
});

// Route::controller(LoginController::class)->group(function () {
//     Route::get('/auth/acessar', 'index')->name('login');
//     Route::post('/auth/login', 'login')->name('auth.login');
//     Route::post('/auth/logout', 'logout')->name('auth.logout');
// });
