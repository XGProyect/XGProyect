<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Adm;

use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class AdministrationLib
{
    public static function haveAccess($userLevel): bool
    {
        return ($userLevel >= 1);
    }

    public static function noAccessMessage(string $mes = ''): void
    {
        self::saveMessage('error', $mes, false);
    }

    public static function installDirExists(): bool
    {
        return (file_exists(PUBLIC_PATH . 'install.php'));
    }

    public static function authorization(string $module): bool
    {
        $cleaned_module_name = strtolower(substr(strrchr($module, '\\'), 1));
        $permissions = new Permissions(Options::getInstance()->get('admin_permissions'));

        return $permissions->isAccessAllowed($cleaned_module_name, (int) Users::getInstance()->getUserData()['authlevel']);
    }

    public static function saveMessage(string $result, string $message, bool $dismissible = true): void
    {
        switch ($result) {
            case 'ok':
                $parse['color'] = 'alert-success';
                $parse['status'] = __('admin/global.gn_ok_title');
                break;
            case 'error':
                $parse['color'] = 'alert-danger';
                $parse['status'] = __('admin/global.gn_error_title');
                break;
            case 'warning':
                $parse['color'] = 'alert-warning';
                $parse['status'] = __('admin/global.gn_warning_title');
                break;
            case 'info':
                $parse['color'] = 'alert-info';
                $parse['status'] = '';
                break;
        }

        $parse['message'] = $message;

        if (!$dismissible) {
            $parse['dismissible'] = 'd-none';
        }

        Template::legacyView(
            'admin.save_message',
            $parse
        );
    }

    public static function showPopUp(string $content, string $popupCcontent): string
    {
        return Template::render(
            'admin.popup',
            [
                'content' => $content,
                'popupContent' => $popupCcontent,
            ]
        );
    }

    public static function checkSession(): void
    {
        if (!self::isSessionSet()) {
            $page = filter_input(INPUT_GET, 'page', FILTER_UNSAFE_RAW);

            if ($page != 'login') {
                Functions::redirect(SYSTEM_ROOT . 'admin/?redirect=' . $page);
            }
        }
    }

    private static function isSessionSet(): bool
    {
        return session('admin_id', false) && session('admin_password', false);
    }

    public static function updateRequired(): void
    {
        if (config('version.files') != Options::getInstance()->get('version')) {
            $exclude_pages = ['', 'home', 'update', 'logout'];

            if (isset($_GET['page']) && !in_array($_GET['page'], $exclude_pages)) {
                Functions::redirect(ADM_URL . 'admin.php?page=update');
            }
        }
    }
}
