<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use CiParser;
use Exception;
use Illuminate\Support\Facades\View;
use Xgp\App\Libraries\Functions;

class Template
{
    /**
     * @var CiParser CodeIgniter Parser Class
     *
     * @deprecated 4.0.0
     */
    private $ciParser = null;
    private static ?Template $instance = null;

    public function __construct()
    {
        $this->createNewParser();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Template();
        }

        return self::$instance;
    }

    public function get(string $template_name): ?string
    {
        try {
            $route = VIEWS_DIR . strtr($template_name, ['/' => DIRECTORY_SEPARATOR, '.' => DIRECTORY_SEPARATOR]) . '.php';
            $template = @file_get_contents($route);

            if ($template) { // We got something
                return $template; // Return
            } else {
                // Throw Exception
                throw new Exception('<p>Template not found or empty: <strong>' . $template_name . '</strong><br />
                    Location: <strong>' . $route . '</strong></p>');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function set(string $template = '', array $data = [], bool $return = false): string
    {
        return $this->ciParser->parse($this->get($template), $data, $return);
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
        View::share('version', SYSTEM_VERSION);

        return View::make($template, $data)->render();
    }

    private function createNewParser(): void
    {
        if (!defined('LIB_PATH')) {
            return;
        }

        // require email library
        $parser_library_path = LIB_PATH . 'Ci' . DIRECTORY_SEPARATOR . 'CiParser.php';

        if (!file_exists($parser_library_path)) {
            return;
        }

        // required by the library
        if (!defined('BASEPATH')) {
            define('BASEPATH', RESOURCES_PATH);
        }

        // use CI library
        require_once $parser_library_path;

        $this->ciParser = new CiParser();
    }
}
