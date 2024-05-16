<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home\Ajax;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class InfoController extends BaseController
{
    public function __invoke(): View | Factory
    {
        return view('home.ajax.info');
    }
}
