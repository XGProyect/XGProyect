<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('INSTALLED', 'false') === 'true') {
            $redirect = redirect();

            if ($redirect instanceof Redirector) {
                return $redirect->route('admin.index');
            }
        }

        return $next($request);
    }
}
