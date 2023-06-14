<?php

use App\Http\Controllers\Account\RecoverController;
use App\Http\Controllers\Home\Ajax\HomeController;
use App\Http\Controllers\Home\Ajax\InfoController;
use App\Http\Controllers\Home\Ajax\MediaController;
use App\Http\Controllers\Home\LoginController;
use App\Http\Controllers\Home\RegisterController;
use App\Http\Controllers\Home\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', WelcomeController::class)->name('index');
    Route::post('/login', LoginController::class)->name('login');
    Route::post('/register', RegisterController::class)->name('register');
});

Route::prefix('home/ajax')->group(function () {
    Route::get('/home', HomeController::class)->name('ajax_home');
    Route::get('/info', InfoController::class)->name('ajax_info');
    Route::get('/media', MediaController::class)->name('ajax_media');
});

Route::prefix('account')->group(function () {
    Route::get('/recover', RecoverController::class)->name('recover');
    Route::post('/recover', [RecoverController::class, 'recover']);
});
