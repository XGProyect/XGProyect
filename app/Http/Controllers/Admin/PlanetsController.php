<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanetsController extends AdminSettingsController
{
    private const INT_SETTINGS = [
        'initial_fields',
        'metal_basic_income',
        'crystal_basic_income',
        'deuterium_basic_income',
        'energy_basic_income',
    ];

    public function __construct(AdministrationService $administrationService, SettingsService $settings)
    {
        parent::__construct($administrationService, $settings);
    }

    public function index(): View
    {
        $this->authorize();

        return $this->view('admin.planets', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize();

        foreach (self::INT_SETTINGS as $field) {
            if ($request->filled($field) && is_numeric($request->input($field))) {
                $value = (int) $request->input($field);

                if ($value >= 0) {
                    $this->settings->write($field, $value);
                }
            }
        }

        return $this->saved('admin.planets', 'admin/planets.np_all_ok_message');
    }

    private function buildViewData(): array
    {
        return array_combine(
            self::INT_SETTINGS,
            array_map(fn ($key) => $this->settings->getInt($key), self::INT_SETTINGS),
        );
    }
}
