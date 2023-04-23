<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class RegistrationController extends BaseController
{
    public const REGISTRATION_SETTINGS = [
        'reg_enable' => FILTER_UNSAFE_RAW,
        'reg_welcome_message' => FILTER_UNSAFE_RAW,
        'reg_welcome_email' => FILTER_UNSAFE_RAW,
    ];

    private string $alert = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        $this->runAction();

        Template::getInstance()->view(
            'admin.registration_view',
            array_merge(
                $this->getNewUserRegistrationSettings(),
                [
                    'alert' => $this->alert ?? '',
                ]
            )
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::REGISTRATION_SETTINGS, true);

        if ($data) {
            foreach ($data as $option => $value) {
                Functions::updateConfig($option, ($value == 'on' ? 1 : 0));
            }

            $this->alert = Administration::saveMessage('ok', $this->langs->line('ur_all_ok_message'));
        }
    }

    private function getNewUserRegistrationSettings(): array
    {
        return $this->setChecked(
            array_filter(
                Functions::readConfig('', true),
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
