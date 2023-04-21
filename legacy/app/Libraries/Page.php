<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class Page
{
    private string $current_year;
    private static ?Page $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Page(new Users());
        }

        return self::$instance;
    }

    public function __construct(Users $users)
    {
        $this->current_year = date('Y');
    }

    /**
     * Display the installation page
     *
     * @param string $current_page
     * @param array $langs
     * @return void
     */
    public function displayInstall($current_page, $langs): void
    {
        $page = $this->installHeader();
        $page .= $this->installMenu($langs); // MENU
        $page .= $this->installNavbar($langs); // TOP NAVIGATION BAR
        $page .= $current_page;
        $page .= Template::getInstance()->render(
            'install.simple_footer',
            ['year' => $this->current_year]
        );

        // Show result page
        die($page);
    }

    private function installHeader(): string
    {
        return Template::getInstance()->render(
            'install.simple_header',
            [
                'title' => 'Install',
                'lang_code' => __('installation/installation.lang_code'),
                'js_path' => '../js/',
                'css_path' => '../css/',
            ]
        );
    }

    /**
     * installNavbar
     *
     * @return string
     */
    private function installNavbar($langs)
    {
        // Update config language to the new setted value
        if (isset($_POST['language'])) {
            Functions::setCurrentLanguage($_POST['language']);
            Functions::redirect(SYSTEM_ROOT . DIRECTORY_SEPARATOR);
        }

        $current_page = isset($_GET['page']) ? $_GET['page'] : null;
        $items = '';

        $pages = [
            0 => ['installation', $langs['ins_overview'], 'overview'],
            1 => ['installation', $langs['ins_license'], 'license'],
            2 => ['installation', $langs['ins_install'], 'step1'],
        ];

        // BUILD THE MENU
        foreach ($pages as $key => $data) {
            if ($data[2] != '') {
                // URL
                $items .= '<li' . ($current_page == $data[0] ? ' class="active"' : '') .
                    '><a href="install.php?page=' . $data[0] . '&mode=' . $data[2] . '">' . $data[1] . '</a></li>';
            } else {
                // URL
                $items .= '<li' . ($current_page == $data[0] ? ' class="active"' : '') .
                    '><a href="install.php?page=' . $data[0] . '">' . $data[1] . '</a></li>';
            }
        }

        // PARSE THE MENU AND OTHER DATA
        $parse = $langs;
        $parse['menu_items'] = $items;
        $parse['language_select'] = Functions::getLanguages(Functions::getCurrentLanguage());

        return Template::getInstance()->render(
            'install/topnav_view',
            $parse
        );
    }

    /**
     * installMenu
     *
     * @return string
     */
    private function installMenu($langs)
    {
        $current_mode = isset($_GET['mode']) ? $_GET['mode'] : null;
        $items = '';
        $steps = [
            0 => ['step1', $langs['ins_step1']],
            1 => ['step2', $langs['ins_step2']],
            2 => ['step3', $langs['ins_step3']],
            3 => ['step4', $langs['ins_step4']],
            4 => ['step5', $langs['ins_step5']],
        ];

        // BUILD THE MENU
        foreach ($steps as $key => $data) {
            // URL
            $items .= '<li' . ($current_mode == $data[0] ? ' class="active"' : '') .
                '><a href="#">' . $data[1] . '</a></li>';
        }

        // PARSE THE MENU AND OTHER DATA
        $parse = $langs;
        $parse['menu_items'] = $items;

        return Template::getInstance()->render(
            'install/menu_view',
            $parse
        );
    }

    /**
     * Removes speacial chars like tabs, new lines and carriage return.
     *
     * @param string $template Template
     *
     * @return string
     */
    public function jsReady($template = '')
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
