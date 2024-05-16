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
        // @phpstan-ignore-next-line
        session(['last_step' => $request->route()->getName()]);

        return view(
            'install.view',
            [
                'license' => Storage::disk('local')->get('license.txt'),
            ]
        );
    }
}
