<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));
Route::get('/login',  [LoginController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationController::class, 'index'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/register/verify', [RegistrationController::class, 'verifyOtpIndex'])->name('register.verify');
    Route::post('/register/verify', [RegistrationController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('register.verify.submit');
    Route::post('/register/resend', [RegistrationController::class, 'resendOtp'])->middleware('throttle:3,1')->name('register.resend');

    Route::get('/forgot-password', [PasswordResetController::class, 'index'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/forgot-password/verify', [PasswordResetController::class, 'verifyOtpIndex'])->name('password.verify');
    Route::post('/forgot-password/verify', [PasswordResetController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('password.verify.submit');
    Route::post('/forgot-password/resend', [PasswordResetController::class, 'resendOtp'])->middleware('throttle:3,1')->name('password.resend');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.store');
});
