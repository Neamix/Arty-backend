<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagment\Http\Controllers\FieldController;
use Modules\ProjectManagment\Http\Controllers\FieldOptionController;
use Modules\ProjectManagment\Http\Controllers\FormController;
use Modules\ProjectManagment\Http\Controllers\ProjectController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('projects', ProjectController::class);

    Route::get('projects/{project}/form', [FormController::class, 'show'])->name('projects.form.show');

    Route::apiResource('projects.fields', FieldController::class);
    Route::apiResource('projects.fields.options', FieldOptionController::class);
});
