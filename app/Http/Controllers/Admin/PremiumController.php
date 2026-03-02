<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class PremiumController extends BaseController
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

        Template::legacyView('admin.premium', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        if ($request->filled('premium_url')) {
            $url = filter_var($request->input('premium_url'), FILTER_VALIDATE_URL);

            if ($url !== false) {
                $this->settings->write('premium_url', $url);
            }
        }

        $floatFields = [
            'merchant_price',
            'merchant_base_min_exchange_rate',
            'merchant_base_max_exchange_rate',
            'merchant_metal_multiplier',
            'merchant_crystal_multiplier',
            'merchant_deuterium_multiplier',
        ];

        foreach ($floatFields as $field) {
            if ($request->filled($field) && is_numeric($request->input($field))) {
                $value = (float) $request->input($field);

                if ($value >= 0) {
                    $this->settings->write($field, $value);
                }
            }
        }

        if ($request->filled('registration_dark_matter') && is_numeric($request->input('registration_dark_matter'))) {
            $value = (int) $request->input('registration_dark_matter');

            if ($value >= 0) {
                $this->settings->write('registration_dark_matter', $value);
            }
        }

        return redirect()->route('admin.premium')
            ->with('success', __('admin/premium.pr_all_ok_message'));
    }

    private function buildViewData(): array
    {
        return [
            'premium_url'                      => $this->settings->getString('premium_url'),
            'registration_dark_matter'         => $this->settings->getInt('registration_dark_matter'),
            'merchant_price'                   => $this->settings->getFloat('merchant_price'),
            'merchant_base_min_exchange_rate'  => $this->settings->getFloat('merchant_base_min_exchange_rate'),
            'merchant_base_max_exchange_rate'  => $this->settings->getFloat('merchant_base_max_exchange_rate'),
            'merchant_metal_multiplier'        => $this->settings->getFloat('merchant_metal_multiplier'),
            'merchant_crystal_multiplier'      => $this->settings->getFloat('merchant_crystal_multiplier'),
            'merchant_deuterium_multiplier'    => $this->settings->getFloat('merchant_deuterium_multiplier'),
        ];
    }
}
