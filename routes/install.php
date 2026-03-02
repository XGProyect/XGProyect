<?php

declare(strict_types=1);

use App\Http\Controllers\Install;
use App\Http\Controllers\Install\Steps;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->group(function () {
    Route::get('/', Install\IndexController::class)->name('install.index');
    Route::get('/set-locale/{locale}', Install\SetLocaleController::class)->name('install.set.locale');
    Route::get('/requirements', Steps\RequirementsController::class)->name('install.step.requirements');
    Route::get('/database', Steps\DatabaseController::class)->name('install.step.database');
    Route::post('/database', [Steps\DatabaseController::class, 'doCheck'])->name('install.step.database.check');
    Route::get('/tables', Steps\TablesController::class)->name('install.step.tables');
    Route::get('/admin', Steps\AdminController::class)->name('install.step.admin');
    Route::post('/admin', [Steps\AdminController::class, 'doCheck'])->name('install.step.admin.check');
    Route::get('/final', Steps\FinalController::class)->name('install.step.final');
});
