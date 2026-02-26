<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Xgp\App\Core\Common;

class LegacyAdminBootstrap
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!defined('IN_ADMIN')) {
            define('IN_ADMIN', true);
            define('XGP_ROOT', base_path('legacy') . DIRECTORY_SEPARATOR);
            require XGP_ROOT . 'app/Core/Common.php';
            (new Common())->bootUp('admin');
        }

        return $next($request);
    }
}
