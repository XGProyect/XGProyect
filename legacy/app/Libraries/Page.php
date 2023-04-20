<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;

class Page
{
    private ?array $current_user;
    private string $current_year;
    private Template $template;
    private static ?Page $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Page(new Users());
        }

        return self::$instance;
    }

    public function __construct(object $users)
    {
        $this->current_user = $users->getUserData();
        $this->current_year = date('Y');

        $this->setTemplate();
    }

    private function setTemplate(): void
    {
        $this->template = new Template();
    }

    public function display(string $current_page, bool $topnav = true, $metatags = '', $menu = true): void
    {
        $page = '';

        if (!defined('IN_MESSAGE')) {
            // For the Home page
            if (defined('IN_LOGIN')) {
                die($current_page);
            }
        }

        // Merge: Header + Topnav + Menu + Page
        if (!defined('IN_INSTALL')) {
            $page .= "\n<center>\n" . $current_page . "\n</center>\n";
        } else {
            if (defined('IN_MESSAGE')) {
                $page .= "\n<center>\n" . $current_page . "\n</center>\n";
            } else {
                $page .= $current_page;
            }
        }

        // Show result page
        die($page);
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

    /**
     * Display the admin page
     *
     * @param string $current_page
     * @param boolean $sidebar
     * @param boolean $navigation
     * @param boolean $footer
     * @return void
     */
    public function displayAdmin(string $current_page, bool $sidebar = true, bool $navigation = true, bool $footer = true): void
    {
        if ($sidebar) {
            $parse['sidebar'] = $this->adminSidebar();
        }

        if ($navigation) {
            $parse['navigation'] = $this->adminNavigation();
        }

        if ($footer) {
            $parse['footer'] = $this->adminFooter();
        }

        $page = $this->adminSimpleHeader();
        $page .= $this->adminPage($current_page, ($parse ?? []), ($sidebar && $navigation && $footer));
        $page .= $this->adminSimpleFooter();

        // Show result page
        die($page);
    }

    /**
     * Set the admin page
     *
     * @param string $page
     * @param array $parse
     * @return string
     */
    private function adminPage(string $page, array $parse, bool $full): string
    {
        return Template::getInstance()->render(
            ($full ? 'admin.admin_page_view' : 'admin.simple_admin_page_view'),
            array_merge(
                $parse,
                ['page_content' => $page]
            )
        );
    }

    /**
     * Set the admin meta header
     *
     * @return string
     */
    private function adminSimpleHeader(): string
    {
        return Template::getInstance()->render(
            'admin.simple_header',
            [
                'title' => 'Admin CP',
                'admin_public_path' => ADMIN_PUBLIC_PATH,
            ]
        );
    }

    private function adminSidebar(): string
    {
        $current_page = isset($_GET['page']) ? $_GET['page'] : null;
        $items = '';
        $flag = '';
        $pages = [
            ['server', '2'],
            ['mailing', '2'],
            ['modules', '2'],
            ['planets', '2'],
            ['registration', '2'],
            ['statistics', '2'],
            ['premium', '2'],
            ['tasks', '3'],
            ['errors', '3'],
            ['fleets', '3'],
            ['messages', '3'],
            ['maker', '4'],
            ['users', '4'],
            ['alliances', '4'],
            ['languages', '4'],
            ['changelog', '4'],
            ['permissions', '4'],
            ['backup', '5'],
            ['encrypter', '5'],
            ['announcement', '5'],
            ['ban', '5'],
            ['rebuildhighscores', '5'],
            ['update', '5'],
            ['repair', '6'],
            ['reset', '6'],
        ];
        $active_block = 1;

        // BUILD THE MENU
        foreach ($pages as $key => $data) {
            $extra = '';
            $active = '';

            if ($data[1] != $flag) {
                $flag = $data[1];
                $items = '';
            }

            if ($data[0] == 'rebuildhighscores') {
                $extra = 'onClick="return confirm(\'' . $lang->line('tools_manual_update_confirm') . '\');"';
            }

            if ($data[0] == $current_page) {
                $active = ' active';
                $active_block = $data[1];
            }

            $items .= '<a class="collapse-item' . $active . '" href="' . ADM_URL . 'admin.php?page=' . $data[0] . '"  ' . $extra . '>' . $lang->line($data[0]) . '</a>';

            $parse_block[$data[1]] = $items;
        }

        // PARSE THE MENU AND OTHER DATA
        $parse = $lang->language;
        $parse['menu_block_2'] = $parse_block[2];
        $parse['menu_block_3'] = $parse_block[3];
        $parse['menu_block_4'] = $parse_block[4];
        $parse['menu_block_5'] = $parse_block[5];
        $parse['menu_block_6'] = $parse_block[6];
        $parse['active_1'] = '';
        $parse['active_1_show'] = '';
        $parse['active_2'] = '';
        $parse['active_2_show'] = '';
        $parse['active_3'] = '';
        $parse['active_3_show'] = '';
        $parse['active_4'] = '';
        $parse['active_4_show'] = '';
        $parse['active_5'] = '';
        $parse['active_5_show'] = '';
        $parse['active_6'] = '';
        $parse['active_6_show'] = '';
        $parse['active_' . $active_block] = ' active';
        $parse['active_' . $active_block . '_show'] = ' show';

        return Template::getInstance()->render(
            'admin.sidebar_view',
            $parse
        );
    }

    /**
     * Set the admin navigation
     *
     * @return string
     */
    private function adminNavigation(): string
    {
        return Template::getInstance()->render(
            'admin.navigation_view',
            [
                'user_name' => $this->current_user['user_name'],
                'current_date' => Timing::formatShortDate(time()),
            ]
        );
    }

    /**
     * Set the admin footer
     *
     * @return string
     */
    private function adminFooter(): string
    {
        return Template::getInstance()->render(
            'admin.footer_view',
            [
                'version' => SYSTEM_VERSION,
                'year' => $this->current_year,
            ]
        );
    }

    /**
     * Set admin simple footer
     *
     * @return string
     */
    private function adminSimpleFooter(): string
    {
        return Template::getInstance()->render(
            'admin.simple_footer',
            [
                'admin_public_path' => ADMIN_PUBLIC_PATH,
                'version' => SYSTEM_VERSION,
            ]
        );
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
