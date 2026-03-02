<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class PlanetsController extends BaseController
{
    private AdministrationService $administrationService;

    public function __construct(private readonly SettingsService $settings)
    {
        $this->administrationService = new AdministrationService($settings);
    }

    public function index(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        Template::legacyView('admin.planets', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $intFields = [
            'initial_fields',
            'metal_basic_income',
            'crystal_basic_income',
            'deuterium_basic_income',
            'energy_basic_income',
        ];

        foreach ($intFields as $field) {
            if ($request->filled($field) && is_numeric($request->input($field))) {
                $value = (int) $request->input($field);

                if ($value >= 0) {
                    $this->settings->write($field, $value);
                }
            }
        }

        return redirect()->route('admin.planets')
            ->with('success', __('admin/planets.np_all_ok_message'));
    }

    private function buildViewData(): array
    {
        return [
            'initial_fields'          => $this->settings->getInt('initial_fields'),
            'metal_basic_income'      => $this->settings->getInt('metal_basic_income'),
            'crystal_basic_income'    => $this->settings->getInt('crystal_basic_income'),
            'deuterium_basic_income'  => $this->settings->getInt('deuterium_basic_income'),
            'energy_basic_income'     => $this->settings->getInt('energy_basic_income'),
        ];
    }
}
