<?php

use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\ExamApplicationController;
use App\Http\Controllers\Applicant\LookupController;
use Illuminate\Support\Facades\Route;

// Applicant application form (three steps)
Route::middleware(['auth', 'role:applicant'])->prefix('dashboard/application')->name('applicant.')->group(function () {
    Route::get('personal', [ApplicationController::class, 'personal'])->name('personal');
    Route::post('personal', [ApplicationController::class, 'savePersonal'])->name('personal.save');
    Route::get('qualifications', [ApplicationController::class, 'qualifications'])->name('qualifications');
    Route::post('qualifications', [ApplicationController::class, 'saveQualifications'])->name('qualifications.save');
    Route::get('trainings', [ApplicationController::class, 'trainings'])->name('trainings');
    Route::post('trainings', [ApplicationController::class, 'saveTrainings'])->name('trainings.save');

    // Applying for exams (application calls)
    Route::get('exams', [ExamApplicationController::class, 'index'])->name('exams');
    Route::get('exams/{exam}/apply', [ExamApplicationController::class, 'create'])->name('exams.apply.form');
    Route::post('exams/{exam}/apply', [ExamApplicationController::class, 'apply'])->middleware('throttle:10,1')->name('exams.apply');
    Route::get('my-applications', [ExamApplicationController::class, 'applications'])->name('applications');
    Route::get('my-applications/{application}', [ExamApplicationController::class, 'show'])->name('applications.show');
});

// Lookups used by the form, plus viewing uploaded files (owner or admin only)
Route::middleware('auth')->prefix('lookup')->name('lookup.')->group(function () {
    Route::get('districts/{district}/municipalities', [LookupController::class, 'municipalities'])->name('municipalities');
    Route::get('municipalities/{municipality}/wards', [LookupController::class, 'wards'])->name('wards');
    Route::get('date/convert', [LookupController::class, 'convertDate'])->middleware('throttle:120,1')->name('date.convert');
    Route::get('media/{media}', [LookupController::class, 'media'])->name('media');
});
