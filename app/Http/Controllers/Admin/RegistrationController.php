<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrationController extends AdminSettingsController
{
    private const BOOL_SETTINGS = [
        'reg_enable',
        'reg_welcome_message',
        'reg_welcome_email',
    ];

    public function __construct(AdministrationService $administrationService, SettingsService $settings)
    {
        parent::__construct($administrationService, $settings);
    }

    public function index(): View
    {
        $this->authorize();

        return $this->view('admin.registration', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize();

        foreach (self::BOOL_SETTINGS as $key) {
            $this->settings->write($key, $request->boolean($key) ? 1 : 0);
        }

        return $this->saved('admin.registration', 'admin/registration.ur_all_ok_message');
    }

    private function buildViewData(): array
    {
        return array_combine(
            self::BOOL_SETTINGS,
            array_map(fn ($key) => $this->settings->getBool($key), self::BOOL_SETTINGS),
        );
    }
}
