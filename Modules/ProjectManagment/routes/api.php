<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagment\Http\Controllers\BoardController;
use Modules\ProjectManagment\Http\Controllers\FieldController;
use Modules\ProjectManagment\Http\Controllers\FieldOptionController;
use Modules\ProjectManagment\Http\Controllers\FormController;
use Modules\ProjectManagment\Http\Controllers\LeadController;
use Modules\ProjectManagment\Http\Controllers\ProjectController;
use Modules\ProjectManagment\Http\Controllers\StageController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::get('projects/{project}/board', [BoardController::class, 'show'])->name('projects.board.show');
    Route::get('projects/{project}/kanban', [BoardController::class, 'kanban'])->name('projects.kanban.show');
    Route::get('projects/{project}/sheet', [BoardController::class, 'sheet'])->name('projects.sheet.show');
    Route::get('projects/{project}/calendar', [BoardController::class, 'calendar'])->name('projects.calendar.show');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::patch('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('projects/{project}/form', [FormController::class, 'show'])->name('projects.form.show');
    Route::put('projects/{project}/form', [FormController::class, 'update'])->name('projects.form.update');
    Route::patch('projects/{project}/form', [FormController::class, 'update']);

    Route::get('projects/{project}/stages', [StageController::class, 'index'])->name('projects.stages.index');
    Route::post('projects/{project}/stages', [StageController::class, 'store'])->name('projects.stages.store');
    Route::get('projects/{project}/stages/{stage}', [StageController::class, 'show'])->name('projects.stages.show');
    Route::put('projects/{project}/stages/{stage}', [StageController::class, 'update'])->name('projects.stages.update');
    Route::patch('projects/{project}/stages/{stage}', [StageController::class, 'update']);
    Route::delete('projects/{project}/stages/{stage}', [StageController::class, 'destroy'])->name('projects.stages.destroy');

    Route::get('projects/{project}/stages/{stage}/leads', [LeadController::class, 'index'])->name('projects.stages.leads.index');
    Route::post('projects/{project}/stages/{stage}/leads', [LeadController::class, 'store'])->name('projects.stages.leads.store');
    Route::get('projects/{project}/stages/{stage}/leads/{lead}', [LeadController::class, 'show'])->name('projects.stages.leads.show');
    Route::put('projects/{project}/stages/{stage}/leads/{lead}', [LeadController::class, 'update'])->name('projects.stages.leads.update');
    Route::patch('projects/{project}/stages/{stage}/leads/{lead}', [LeadController::class, 'update']);
    Route::delete('projects/{project}/stages/{stage}/leads/{lead}', [LeadController::class, 'destroy'])->name('projects.stages.leads.destroy');

    Route::get('projects/{project}/fields', [FieldController::class, 'index'])->name('projects.fields.index');
    Route::post('projects/{project}/fields', [FieldController::class, 'store'])->name('projects.fields.store');
    Route::get('projects/{project}/fields/{field}', [FieldController::class, 'show'])->name('projects.fields.show');
    Route::put('projects/{project}/fields/{field}', [FieldController::class, 'update'])->name('projects.fields.update');
    Route::patch('projects/{project}/fields/{field}', [FieldController::class, 'update']);
    Route::delete('projects/{project}/fields/{field}', [FieldController::class, 'destroy'])->name('projects.fields.destroy');

    Route::get('projects/{project}/fields/{field}/options', [FieldOptionController::class, 'index'])->name('projects.fields.options.index');
    Route::post('projects/{project}/fields/{field}/options', [FieldOptionController::class, 'store'])->name('projects.fields.options.store');
    Route::get('projects/{project}/fields/{field}/options/{option}', [FieldOptionController::class, 'show'])->name('projects.fields.options.show');
    Route::put('projects/{project}/fields/{field}/options/{option}', [FieldOptionController::class, 'update'])->name('projects.fields.options.update');
    Route::patch('projects/{project}/fields/{field}/options/{option}', [FieldOptionController::class, 'update']);
    Route::delete('projects/{project}/fields/{field}/options/{option}', [FieldOptionController::class, 'destroy'])->name('projects.fields.options.destroy');
});
