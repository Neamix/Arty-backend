<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagement\Http\Controllers\AttachmentController;
use Modules\ProjectManagement\Http\Controllers\LeadController;
use Modules\ProjectManagement\Http\Controllers\ProjectController;
use Modules\ProjectManagement\Http\Controllers\StageController;

Route::middleware('auth:api')->prefix('v1')->name('projects.')->group(function () {
    Route::get('projects', [ProjectController::class, 'index'])->name('index');
    Route::post('projects', [ProjectController::class, 'store'])->name('store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('destroy');

    Route::scopeBindings()->group(function () {
        Route::prefix('projects/{project}/stages')->name('stages.')->group(function () {
            Route::post('/', [StageController::class, 'store'])->name('store');
            Route::post('reorder', [StageController::class, 'reorder'])->name('reorder');
            Route::get('{stage}/leads', [StageController::class, 'leads'])->name('leads');
            Route::put('{stage}', [StageController::class, 'update'])->name('update');
            Route::delete('{stage}', [StageController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('projects/{project}/leads')->name('leads.')->group(function () {
            Route::get('/', [LeadController::class, 'index'])->name('index');
            Route::post('/', [LeadController::class, 'store'])->name('store');
            Route::put('{lead}', [LeadController::class, 'update'])->name('update');
            Route::delete('{lead}', [LeadController::class, 'destroy'])->name('destroy');
            Route::patch('{lead}/move', [LeadController::class, 'move'])->name('move');

            Route::prefix('{lead}/attachments')->name('attachments.')->group(function () {
                Route::get('/', [AttachmentController::class, 'index'])->name('index');
                Route::post('/', [AttachmentController::class, 'store'])->name('store');
                Route::delete('{attachment}', [AttachmentController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
