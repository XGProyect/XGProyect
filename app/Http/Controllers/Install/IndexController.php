<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class IndexController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        $route = $request->route();

        session(['last_step' => $route !== null ? $route->getName() : null]);

        return view(
            'install.view',
            [
                'license' => Storage::disk('local')->get('license.txt'),
            ]
        );
    }
}
