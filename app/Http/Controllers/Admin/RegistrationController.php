<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class RegistrationController extends BaseController
{
    private const BOOL_SETTINGS = [
        'reg_enable',
        'reg_welcome_message',
        'reg_welcome_email',
    ];

    private AdministrationService $administrationService;

    public function __construct(private readonly SettingsService $settings)
    {
        $this->administrationService = new AdministrationService($settings);
    }

    public function index(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        Template::legacyView('admin.registration', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        foreach (self::BOOL_SETTINGS as $key) {
            $this->settings->write($key, $request->boolean($key) ? 1 : 0);
        }

        return redirect()->route('admin.registration')
            ->with('success', __('admin/registration.ur_all_ok_message'));
    }

    private function buildViewData(): array
    {
        return [
            'reg_enable'          => $this->settings->getBool('reg_enable'),
            'reg_welcome_message' => $this->settings->getBool('reg_welcome_message'),
            'reg_welcome_email'   => $this->settings->getBool('reg_welcome_email'),
        ];
    }
}
