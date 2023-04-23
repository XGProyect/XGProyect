<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class LegacyController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        ob_start();
        $file = strtr($request->getPathInfo(), ['/' => '', '.php' => '']);

        if (empty($file)) {
            $file = 'index';
        }

        if (in_array($file, ['index', 'install', 'admin', 'ajax', 'game'])) {
            require app_path('Http') . '/' . $file . '.php';
        }

        $output = ob_get_clean();

        return new Response($output);
    }
}
