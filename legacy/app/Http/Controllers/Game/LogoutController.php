<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Libraries\Functions;

class LogoutController extends BaseController
{
    public function __invoke(): void
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Functions::redirect('/');
    }
}
