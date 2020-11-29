<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::prefix('/')->group(function () {
    Route::get('/', 'HomeController@index');
    Route::get('/maintenance', 'HomeController@maintenance');
    Route::get('/welcome', 'HomeController@welcome');
    Route::get('/about', 'HomeController@about');
    Route::get('/media', 'HomeController@media');
    Route::post('/signin', 'HomeController@signin');
    Route::get('/signout', 'HomeController@signout');
    Route::post('/register', 'RegisterController@index');
});
