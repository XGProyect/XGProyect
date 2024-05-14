<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;

class PlanetsController extends BaseController
{
    public const PLANET_SETTINGS = [
        'initial_fields' => FILTER_VALIDATE_INT,
        'metal_basic_income' => FILTER_VALIDATE_INT,
        'crystal_basic_income' => FILTER_VALIDATE_INT,
        'deuterium_basic_income' => FILTER_VALIDATE_INT,
        'energy_basic_income' => FILTER_VALIDATE_INT,
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
            'admin.planets',
            $this->getNewPlanetSettings()
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::PLANET_SETTINGS);

        if ($data) {
            $data = array_diff($data, [null, false]);

            foreach ($data as $option => $value) {
                Options::getInstance()->write($option, $value);
            }

            session()->flash('success', __('admin/planets.np_all_ok_message'));
        }
    }

    private function getNewPlanetSettings(): array
    {
        return array_filter(
            Options::getInstance()->get(),
            function ($key) {
                return array_key_exists($key, self::PLANET_SETTINGS);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
