<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;

class PlanetsController extends BaseController
{
    public const PLANET_SETTINGS = [
        'initial_fields' => FILTER_VALIDATE_INT,
        'metal_basic_income' => FILTER_VALIDATE_INT,
        'crystal_basic_income' => FILTER_VALIDATE_INT,
        'deuterium_basic_income' => FILTER_VALIDATE_INT,
        'energy_basic_income' => FILTER_VALIDATE_INT,
    ];

    private string $alert = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::PLANET_SETTINGS);

        if ($data) {
            $data = array_diff($data, [null, false]);

            foreach ($data as $option => $value) {
                Functions::updateConfig($option, $value);
            }

            $this->alert = Administration::saveMessage('ok', $this->langs->line('np_all_ok_message'));
        }
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->render(
                'admin.planets_view',
                array_merge(
                    $this->getNewPlanetSettings(),
                    [
                        'alert' => $this->alert ?? '',
                    ]
                )
            )
        );
    }

    /**
     * Get new planet settings
     *
     * @return void
     */
    private function getNewPlanetSettings(): array
    {
        return array_filter(
            Functions::readConfig('', true),
            function ($key) {
                return array_key_exists($key, self::PLANET_SETTINGS);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
