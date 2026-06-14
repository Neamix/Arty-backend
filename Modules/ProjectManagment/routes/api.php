<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagment\Http\Controllers\ProjectManagmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('projectmanagments', ProjectManagmentController::class)->names('projectmanagment');
});
