<?php

declare(strict_types=1);

namespace App\Services;

use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\Permissions;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class AdministrationService
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    public function checkSession(): void
    {
        if (!$this->isSessionSet()) {
            $page = filter_input(INPUT_GET, 'page', FILTER_UNSAFE_RAW);

            if ($page != 'login') {
                Functions::redirect(SYSTEM_ROOT . 'admin/?redirect=' . $page);
            }
        }
    }

    public function authorization(string $module): void
    {
        $lastOcurrence = strrchr($module, '\\');

        if ($lastOcurrence !== false) {
            $cleanedModuleName = strtolower(substr($lastOcurrence, 1));
            $permissions = new Permissions($this->settingsService->getString('admin_permissions'));

            if ($permissions->isAccessAllowed($cleanedModuleName, (int) Users::getInstance()->getUserData()['authlevel'])) {
                return;
            }
        }

        Template::legacyView('admin.save_message');
        exit;
    }

    public function showPopUp(string $content, string $popupCcontent): string
    {
        return Template::render(
            'admin.popup',
            [
                'content' => $content,
                'popupContent' => $popupCcontent,
            ]
        );
    }

    private function isSessionSet(): bool
    {
        return session('admin_id', false) && session('admin_password', false);
    }
}
