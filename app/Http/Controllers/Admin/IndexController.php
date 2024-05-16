<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        return view(
            'admin.index',
            [
                'redirect' => $request->get('redirect'),
            ]
        );
    }
}
