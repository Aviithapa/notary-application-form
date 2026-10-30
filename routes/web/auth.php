<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
Route::get('/login',  [LoginController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth'])->name('logout');
