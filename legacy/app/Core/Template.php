<?php

namespace Xgp\App\Core;

use CiParser;
use Exception;
use Illuminate\Support\Facades\View;

class Template
{
    /**
     * @var CiParser CodeIgniter Parser Class
     *
     * @deprecated 4.0.0
     */
    private $ciParser = null;

    public function __construct()
    {
        $this->createNewParser();
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
        $route = VIEWS_DIR . strtr($template, ['/' => DIRECTORY_SEPARATOR, '.' => DIRECTORY_SEPARATOR]) . '.blade.php';

        if (file_exists($route)) {
            return $this->setBlade($template, $data);
        }

        return $this->ciParser->parse($this->get($template), $data, $return);
    }

    private function setBlade(string $template = '', array $data = []): string
    {
        return View::make(strtr($template, ['/' => '.']), $data)->render();
    }

    private function createNewParser(): void
    {
        // require email library
        $parser_library_path = XGP_ROOT . LIB_PATH . 'Ci' . DIRECTORY_SEPARATOR . 'CiParser.php';

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
