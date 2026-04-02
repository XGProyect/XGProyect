<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SettingsService;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Xgp\App\Libraries\Adm\Permissions;

class AdminAuthorize
{
    private const ALWAYS_ALLOWED = ['home', 'search'];

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly Guard $auth,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $action = $request->route()?->getAction();

        if (!is_array($action)) {
            abort(403);
        }

        $controller = $action['controller'] ?? null;

        if (!is_string($controller)) {
            abort(403);
        }

        // Strip method suffix (e.g. "App\...\HomeController@index" → "HomeController")
        $classOnly = explode('@', $controller)[0];
        $lastSegment = strrchr($classOnly, '\\');

        if ($lastSegment !== false) {
            $module = strtolower(
                str_ireplace('controller', '', substr($lastSegment, 1))
            );

            if (in_array($module, self::ALWAYS_ALLOWED, true)) {
                return $next($request);
            }

            $permissions = new Permissions($this->settingsService->getString('admin_permissions'));

            /** @var User $authUser */
            $authUser = $this->auth->user();

            if ($permissions->isAccessAllowed($module, (int) $authUser->authlevel)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
