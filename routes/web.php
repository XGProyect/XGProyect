<?php

use App\Http\Controllers\Account\RecoverController;
use App\Http\Controllers\Home\Ajax\HomeController;
use App\Http\Controllers\Home\Ajax\InfoController;
use App\Http\Controllers\Home\Ajax\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('home/ajax')->group(function () {
    Route::get('/home', HomeController::class);
    Route::get('/info', InfoController::class);
    Route::get('/media', MediaController::class);
});

Route::prefix('account')->group(function () {
    Route::get('/recover', RecoverController::class);
    Route::post('/recover', [RecoverController::class, 'recover']);
});
