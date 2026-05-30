<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\AuthController;
use Modules\UserManagement\Http\Controllers\ForgotPasswordController;
use Modules\UserManagement\Http\Controllers\GoogleAuthController;

Route::prefix('v1/auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login');

    Route::prefix('password')->name('password.')->group(function () {
        Route::post('forgot', [ForgotPasswordController::class, 'sendOtp'])
            ->middleware('throttle:5,15')
            ->name('forgot');

        Route::post('verify', [ForgotPasswordController::class, 'verifyOtp'])
            ->middleware('throttle:10,15')
            ->name('verify');

        Route::post('reset', [ForgotPasswordController::class, 'resetPassword'])
            ->middleware('throttle:5,15')
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
});
