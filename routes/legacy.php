<?php

declare(strict_types=1);

use App\Http\Controllers\LegacyController;
use Illuminate\Support\Facades\Route;

Route::any('{path}', LegacyController::class)->where('path', '.*');
