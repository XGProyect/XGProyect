<?php

use App\Http\Controllers\Home\Ajax\HomeController;
use App\Http\Controllers\Home\Ajax\InfoController;
use App\Http\Controllers\Home\Ajax\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('home/ajax')->group(function () {
    Route::any('/home', HomeController::class);
    Route::any('/info', InfoController::class);
    Route::any('/media', MediaController::class);
});
