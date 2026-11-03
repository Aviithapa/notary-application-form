<?php

//Route Dashboard

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
 
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::resource('dashboard/user', UserController::class)->middleware(['auth']);

// CMS 
Route::middleware(['auth', 'admin:admin,super_admin'])->group(function () {
    
    Route::get('change-password', [PasswordResetController::class, 'showChangePassword'])->name('change.password');
    Route::post('change-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');

    // Service management
    Route::resource('dashboard/services', ServiceController::class)->except('show')->names('services');
    Route::resource('dashboard/exams', ExamController::class)->except('show')->names('exams');
    Route::get('dashboard/exams/{exam}/applications', [ExamController::class, 'applications'])->name('exams.applications');
    Route::get('dashboard/exams/{exam}/applications/{application}', [ExamController::class, 'showApplication'])->name('exams.applications.show');

});
