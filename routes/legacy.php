<?php

use App\Http\Controllers\LegacyController;
use Illuminate\Support\Facades\Route;

//Route::get('game.php', EmpireController::class)->where('page', 'empire');
Route::any('{path}', LegacyController::class)->where('path', '.*');
