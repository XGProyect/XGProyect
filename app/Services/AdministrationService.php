<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\Permissions;

class AdministrationService
{
    private const ALWAYS_ALLOWED = ['home'];

    public function __construct(private SettingsService $settingsService)
    {
    }

    public function checkSession(): void
    {
        if (!$this->isSessionSet()) {
            abort(redirect()->route('admin.login'));
        }
    }

    public function authorization(string $module): void
    {
        $lastOcurrence = strrchr($module, '\\');

        if ($lastOcurrence !== false) {
            $cleanedModuleName = strtolower(
                str_ireplace('controller', '', substr($lastOcurrence, 1))
            );

            if (in_array($cleanedModuleName, self::ALWAYS_ALLOWED, true)) {
                return;
            }

            $permissions = new Permissions($this->settingsService->getString('admin_permissions'));

            if ($permissions->isAccessAllowed($cleanedModuleName, (int) Auth::user()->authlevel)) {
                return;
            }
        }

        abort(403);
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
