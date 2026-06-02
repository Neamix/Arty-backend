<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagement\Http\Controllers\ProjectController;
use Modules\ProjectManagement\Http\Controllers\ProjectLeadController;
use Modules\ProjectManagement\Http\Controllers\ProjectStageController;

Route::middleware('auth:api')->prefix('v1')->name('projects.')->group(function () {
    Route::get('projects', [ProjectController::class, 'index'])->name('index');
    Route::post('projects', [ProjectController::class, 'store'])->name('store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('destroy');

    Route::scopeBindings()->group(function () {
        Route::prefix('projects/{project}/stages')->name('stages.')->group(function () {
            Route::post('/', [ProjectStageController::class, 'store'])->name('store');
            Route::post('reorder', [ProjectStageController::class, 'reorder'])->name('reorder');
            Route::get('{stage}/leads', [ProjectStageController::class, 'leads'])->name('leads');
            Route::put('{stage}', [ProjectStageController::class, 'update'])->name('update');
            Route::delete('{stage}', [ProjectStageController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('projects/{project}/leads')->name('leads.')->group(function () {
            Route::get('/', [ProjectLeadController::class, 'index'])->name('index');
            Route::post('/', [ProjectLeadController::class, 'store'])->name('store');
            Route::put('{lead}', [ProjectLeadController::class, 'update'])->name('update');
            Route::delete('{lead}', [ProjectLeadController::class, 'destroy'])->name('destroy');
            Route::patch('{lead}/move', [ProjectLeadController::class, 'move'])->name('move');
        });
    });
});
