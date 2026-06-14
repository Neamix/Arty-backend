<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagment\Http\Controllers\ProjectController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('projects', ProjectController::class);
});
