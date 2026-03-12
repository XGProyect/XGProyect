<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Exceptions\LegacyView;
use App\Services\SettingsService;
use Illuminate\Support\Facades\View;

class Template
{
    /**
     * @deprecated use view() helper whenever possible.
     */
    public static function legacyView($view = null, $data = [], $mergeData = []): void
    {
        View::share('gameTitle', app(SettingsService::class)->getString('game_name'));

        throw new LegacyView(
            view($view, $data, $mergeData)
        );
    }

    public static function render(string $template = '', array $data = [])
    {
        $bladeFile = resource_path('views') . DIRECTORY_SEPARATOR . strtr($template, ['/' => DIRECTORY_SEPARATOR, '.' => DIRECTORY_SEPARATOR]) . '.blade.php';

        if (!file_exists($bladeFile)) {
            return;
        }

        View::share('gameTitle', app(SettingsService::class)->getString('game_name'));

        return View::make($template, $data)->render();
    }

    /**
     * Removes speacial chars like tabs, new lines and carriage return.
     */
    public static function jsReady(string $template = ''): string
    {
        $output = str_replace(["\r\n", "\r"], "\n", $template);
        $lines = explode("\n", $output);
        $new_lines = [];

        foreach ($lines as $i => $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }

        return join($new_lines);
    }
}
