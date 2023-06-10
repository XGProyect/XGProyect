<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use Illuminate\Support\Facades\View;
use Xgp\App\Libraries\Functions;

class Template
{
    private static ?Template $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Template();
        }

        return self::$instance;
    }

    public function view(string $template = '', array $data = []): void
    {
        die(self::render($template, $data));
    }

    public function render(string $template = '', array $data = [])
    {
        $bladeFile = resource_path('views') . DIRECTORY_SEPARATOR . strtr($template, ['/' => DIRECTORY_SEPARATOR, '.' => DIRECTORY_SEPARATOR]) . '.blade.php';

        if (!file_exists($bladeFile)) {
            return;
        }

        View::share('gameTitle', Functions::readConfig('game_name'));
        View::share('version', config('version.files'));

        return View::make($template, $data)->render();
    }
}
