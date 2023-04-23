<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class PremiumController extends BaseController
{
    public const PREMIUM_SETTINGS = [
        'premium_url' => FILTER_VALIDATE_URL,
        'merchant_price' => FILTER_VALIDATE_FLOAT,
        'merchant_base_min_exchange_rate' => FILTER_VALIDATE_FLOAT,
        'merchant_base_max_exchange_rate' => FILTER_VALIDATE_FLOAT,
        'merchant_metal_multiplier' => FILTER_VALIDATE_FLOAT,
        'merchant_crystal_multiplier' => FILTER_VALIDATE_FLOAT,
        'merchant_deuterium_multiplier' => FILTER_VALIDATE_FLOAT,
        'registration_dark_matter' => FILTER_VALIDATE_INT,
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
            'admin.premium',
            array_merge(
                $this->getPremiumSettings(),
                [
                    'alert' => $this->alert ?? '',
                ]
            )
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::PREMIUM_SETTINGS);

        if ($data) {
            $data = array_diff($data, [null, false]);

            foreach ($data as $option => $value) {
                if ((is_numeric($value) && $value >= 0) or is_string($value)) {
                    Functions::updateConfig($option, $value);
                }
            }

            $this->alert = Administration::saveMessage('ok', __('admin/preferences.pr_all_ok_message'));
        }
    }

    private function getPremiumSettings(): array
    {
        return array_filter(
            Functions::readConfig('', true),
            function ($key) {
                return array_key_exists($key, self::PREMIUM_SETTINGS);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
