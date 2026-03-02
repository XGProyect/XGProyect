<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ServerRequest;
use App\Services\Admin\ServerSettingsService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Xgp\App\Libraries\Functions;

class ServerController extends AdminSettingsController
{
    public function __construct(
        SettingsService $settings,
        private readonly ServerSettingsService $serverSettings,
    ) {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.server', $this->buildViewData());
    }

    public function update(ServerRequest $request): RedirectResponse
    {
        foreach ($request->toSettings() as $key => $value) {
            $this->settings->write($key, $value);
        }

        // Side effect: sync app locale when language changes
        if ($request->filled('language')) {
            Functions::setLanguage($request->input('language'));
        }

        return $this->saved('admin.server', 'admin/server.se_all_ok_message');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(): array
    {
        return [
            // Identity
            'game_name' => $this->settings->getString('game_name'),
            'game_logo' => $this->settings->getString('game_logo'),
            'language_settings' => Functions::getLanguages($this->settings->getString('lang')),
            'admin_email' => $this->settings->getString('admin_email'),
            'forum_url' => $this->settings->getString('forum_url'),
            // Speed & Economy
            'game_speed' => $this->settings->getInt('game_speed') / 2500,
            'fleet_speed' => $this->settings->getInt('fleet_speed') / 2500,
            'resource_multiplier' => $this->settings->getString('resource_multiplier'),
            // Server Access
            'game_enable' => $this->settings->getBool('game_enable'),
            'close_reason' => stripslashes($this->settings->getString('close_reason')),
            // Date & Time
            'timezone_options' => $this->serverSettings->timezoneOptions(),
            'date_format' => $this->settings->getString('date_format'),
            'date_format_extended' => $this->settings->getString('date_format_extended'),
            // Combat rules
            'adm_attack' => $this->settings->getBool('adm_attack'),
            'fleet_cdr_options' => $this->serverSettings->percentageOptions($this->settings->getInt('fleet_cdr')),
            'defs_cdr_options' => $this->serverSettings->percentageOptions($this->settings->getInt('defs_cdr')),
            // Noob protection
            'noobprotection' => $this->settings->getBool('noobprotection'),
            'noobprotectiontime' => $this->settings->getInt('noobprotectiontime'),
            'noobprotectionmulti' => $this->settings->getInt('noobprotectionmulti'),
        ];
    }
}
