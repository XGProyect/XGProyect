<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Libraries\Adm\Permissions;

class AdminAuthorize
{
    private const ALWAYS_ALLOWED = ['home'];

    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $action = $request->route()->getAction();

        $controllerClass = $action['controller'] ?? '';

        // Strip method suffix (e.g. "App\...\HomeController@index" → "HomeController")
        $classOnly = explode('@', $controllerClass)[0];
        $lastSegment = strrchr($classOnly, '\\');

        if ($lastSegment !== false) {
            $module = strtolower(
                str_ireplace('controller', '', substr($lastSegment, 1))
            );

            if (in_array($module, self::ALWAYS_ALLOWED, true)) {
                return $next($request);
            }

            $permissions = new Permissions($this->settingsService->getString('admin_permissions'));

            if ($permissions->isAccessAllowed($module, (int) Auth::user()->authlevel)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
