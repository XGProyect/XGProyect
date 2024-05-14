<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Adm;

use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class AdministrationLib
{
    public static function checkSession(): void
    {
        if (!self::isSessionSet()) {
            $page = filter_input(INPUT_GET, 'page', FILTER_UNSAFE_RAW);

            if ($page != 'login') {
                Functions::redirect(SYSTEM_ROOT . 'admin/?redirect=' . $page);
            }
        }
    }

    public static function authorization(string $module): void
    {
        $lastOcurrence = strrchr($module, '\\');

        if ($lastOcurrence !== false) {
            $cleanedModuleName = strtolower(substr($lastOcurrence, 1));
            $permissions = new Permissions(Options::getInstance()->get('admin_permissions'));

            if (!$permissions->isAccessAllowed($cleanedModuleName, (int) Users::getInstance()->getUserData()['authlevel'])) {
                self::saveMessage('error', __('admin/global.no_permissions'), false);
                exit;
            }
        }
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

    private static function isSessionSet(): bool
    {
        return session('admin_id', false) && session('admin_password', false);
    }
}
