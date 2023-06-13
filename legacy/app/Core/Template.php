<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Exceptions\LegacyView;
use Illuminate\Support\Facades\View;
use Xgp\App\Libraries\Functions;

class Template
{
    private static ?Template $instance = null;

    public static function legacyView($view = null, $data = [], $mergeData = []): void
    {
        View::share('gameTitle', Functions::readConfig('game_name'));

        throw new LegacyView(
            view($view, $data, $mergeData)
        );
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Template();
        }

        return self::$instance;
    }

    public function render(string $template = '', array $data = [])
    {
        $bladeFile = resource_path('views') . DIRECTORY_SEPARATOR . strtr($template, ['/' => DIRECTORY_SEPARATOR, '.' => DIRECTORY_SEPARATOR]) . '.blade.php';

        if (!file_exists($bladeFile)) {
            return;
        }

        View::share('gameTitle', Functions::readConfig('game_name'));

        return View::make($template, $data)->render();
    }
}
