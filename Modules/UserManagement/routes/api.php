<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\AuthController;
use Modules\UserManagement\Http\Controllers\EmailVerificationController;
use Modules\UserManagement\Http\Controllers\ForgotPasswordController;
use Modules\UserManagement\Http\Controllers\GoogleAuthController;
use Modules\UserManagement\Http\Controllers\PermissionController;
use Modules\UserManagement\Http\Controllers\RoleController;
use Modules\UserManagement\Http\Controllers\WorkspaceInvitationController;
use Modules\UserManagement\Http\Controllers\WorkspaceMemberController;

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

Route::prefix('v1')->group(function () {
    Route::get('members/invitations/{token}', [WorkspaceInvitationController::class, 'show'])
        ->name('members.invitations.show');
    Route::post('members/invitations/register', [WorkspaceInvitationController::class, 'register'])
        ->name('members.invitations.register');

    Route::middleware('auth:api')->group(function () {
        Route::get('members', [WorkspaceMemberController::class, 'index'])->name('members.index');
        Route::post('members/invite', [WorkspaceMemberController::class, 'invite'])->name('members.invite');
        Route::patch('members/{user}/role', [WorkspaceMemberController::class, 'updateRole'])->name('members.role.update');
        Route::delete('members/{user}', [WorkspaceMemberController::class, 'destroy'])->name('members.destroy');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });
});
