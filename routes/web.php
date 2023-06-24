<?php

use App\Http\Controllers\Account\RecoverController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Home\Ajax\HomeController;
use App\Http\Controllers\Home\Ajax\InfoController;
use App\Http\Controllers\Home\Ajax\MediaController;
use App\Http\Controllers\Home\LoginController;
use App\Http\Controllers\Home\RegisterController;
use App\Http\Controllers\Home\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', WelcomeController::class)->name('home.index');
    Route::post('/login', LoginController::class)->name('home.login');
    Route::post('/register', RegisterController::class)->name('home.register');
});

Route::prefix('home/ajax')->group(function () {
    Route::get('/home', HomeController::class)->name('ajax.home');
    Route::get('/info', InfoController::class)->name('ajax.info');
    Route::get('/media', MediaController::class)->name('ajax.media');
});

Route::prefix('account')->group(function () {
    Route::get('/recover', RecoverController::class)->name('account.recover');
    Route::post('/recover', [RecoverController::class, 'recover']);
});

Route::prefix('admin')->group(function () {
    Route::get('/', Admin\IndexController::class)->name('admin.index');
    Route::post('/login', Admin\LoginController::class)->name('admin.login');
    Route::get('/logout', Admin\LogoutController::class)->name('admin.logout');
});
