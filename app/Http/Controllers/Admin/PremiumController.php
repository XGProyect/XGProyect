<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PremiumRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PremiumController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.premium', $this->buildViewData());
    }

    public function update(PremiumRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $field => $value) {
            if ($value !== null) {
                $this->settings->write($field, $value);
            }
        }

        return $this->saved('admin.premium', 'admin/premium.pr_all_ok_message');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(): array
    {
        return [
            'premium_url' => $this->settings->getString('premium_url'),
            'registration_dark_matter' => $this->settings->getInt('registration_dark_matter'),
            'merchant_price' => $this->settings->getFloat('merchant_price'),
            'merchant_base_min_exchange_rate' => $this->settings->getFloat('merchant_base_min_exchange_rate'),
            'merchant_base_max_exchange_rate' => $this->settings->getFloat('merchant_base_max_exchange_rate'),
            'merchant_metal_multiplier' => $this->settings->getFloat('merchant_metal_multiplier'),
            'merchant_crystal_multiplier' => $this->settings->getFloat('merchant_crystal_multiplier'),
            'merchant_deuterium_multiplier' => $this->settings->getFloat('merchant_deuterium_multiplier'),
        ];
    }
}
