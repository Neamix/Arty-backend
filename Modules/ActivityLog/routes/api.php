<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;

Route::middleware('auth:api')->prefix('v1')->name('activity-logs.')->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('index');
});
