<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;

class RegistrationController extends BaseController
{
    public const REGISTRATION_SETTINGS = [
        'reg_enable' => FILTER_UNSAFE_RAW,
        'reg_welcome_message' => FILTER_UNSAFE_RAW,
        'reg_welcome_email' => FILTER_UNSAFE_RAW,
    ];
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.registration',
            $this->getNewUserRegistrationSettings()
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::REGISTRATION_SETTINGS, true);

        if ($data) {
            foreach ($data as $option => $value) {
                Options::getInstance()->write($option, ($value == 'on' ? 1 : 0));
            }

            session()->flash('success', __('admin/registration.ur_all_ok_message'));
        }
    }

    private function getNewUserRegistrationSettings(): array
    {
        return $this->setChecked(
            array_filter(
                Options::getInstance()->get(),
                function ($key) {
                    return array_key_exists($key, self::REGISTRATION_SETTINGS);
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    private function setChecked(array $settings): array
    {
        foreach ($settings as $key => $value) {
            $settings[$key] = $value == 1 ? 'checked="checked"' : '';
        }

        return $settings;
    }
}
