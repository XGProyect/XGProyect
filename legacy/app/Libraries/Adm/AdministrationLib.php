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

            if ($permissions->isAccessAllowed($cleanedModuleName, (int) Users::getInstance()->getUserData()['authlevel'])) {
                return;
            }
        }

        Template::legacyView('admin.save_message');
        exit;
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
