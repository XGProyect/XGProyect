<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlanetsRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PlanetsController extends AdminSettingsController
{
    private const INT_SETTINGS = [
        'initial_fields',
        'metal_basic_income',
        'crystal_basic_income',
        'deuterium_basic_income',
        'energy_basic_income',
    ];

    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.planets', $this->buildViewData());
    }

    public function update(PlanetsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $field => $value) {
            if ($value !== null) {
                $this->settings->write($field, (int) $value);
            }
        }

        return $this->saved('admin.planets', 'admin/planets.np_all_ok_message');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(): array
    {
        return array_combine(
            self::INT_SETTINGS,
            array_map(fn ($key) => $this->settings->getInt($key), self::INT_SETTINGS),
        );
    }
}
