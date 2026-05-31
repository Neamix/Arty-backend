<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\AuthController;
use Modules\UserManagement\Http\Controllers\EmailVerificationController;
use Modules\UserManagement\Http\Controllers\ForgotPasswordController;
use Modules\UserManagement\Http\Controllers\GoogleAuthController;

Route::prefix('v1/auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login');

    Route::prefix('password')->name('password.')->group(function () {
        Route::post('forgot', [ForgotPasswordController::class, 'sendOtp'])
            ->middleware('throttle:10,1')
            ->name('forgot');

        Route::post('verify', [ForgotPasswordController::class, 'verifyOtp'])
            ->middleware('throttle:10,1')
            ->name('verify');

        Route::post('reset', [ForgotPasswordController::class, 'resetPassword'])
            ->middleware('throttle:10,1')
            ->name('reset');
    });

    Route::prefix('google')->name('google.')->group(function () {
        Route::get('redirect', [GoogleAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [GoogleAuthController::class, 'callback'])->name('callback');
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });

    Route::middleware(['auth:api'])->prefix('email')->name('email.')->group(function () {
        Route::post('verify', [EmailVerificationController::class, 'verify'])
            ->name('verify');

        Route::post('resend', [EmailVerificationController::class, 'resend'])
            ->name('resend');
    });
});
