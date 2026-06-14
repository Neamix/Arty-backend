<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagment\Http\Controllers\ProjectManagmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('projectmanagments', ProjectManagmentController::class)->names('projectmanagment');
});
