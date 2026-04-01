<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class LogoutController extends BaseController
{
    public function __construct(private AuthManager $auth)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
