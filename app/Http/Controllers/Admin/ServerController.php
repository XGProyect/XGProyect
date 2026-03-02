<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\Admin\ServerSettingsService;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Functions;

class ServerController extends AdminSettingsController
{
    public function __construct(
        AdministrationService $administrationService,
        SettingsService $settings,
        private readonly ServerSettingsService $serverSettings,
    ) {
        parent::__construct($administrationService, $settings);
    }

    public function index(): View
    {
        $this->authorize();

        return $this->view('admin.server', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize();

        // Identity
        if ($request->filled('game_name')) {
            $this->settings->write('game_name', $request->input('game_name'));
        }

        if ($request->filled('game_logo')) {
            $this->settings->write('game_logo', $request->input('game_logo'));
        }

        if ($request->filled('language')) {
            $lang = $request->input('language');
            $this->settings->write('lang', $lang);
            Functions::setLanguage($lang);
        }

        if ($request->filled('admin_email') && Functions::validEmail($request->input('admin_email'))) {
            $this->settings->write('admin_email', $request->input('admin_email'));
        }

        if ($request->filled('forum_url')) {
            $this->settings->write('forum_url', UrlHelper::prepUrl($request->input('forum_url')));
        }

        // Speed & Economy
        if ($request->filled('game_speed') && is_numeric($request->input('game_speed'))) {
            $this->settings->write('game_speed', 2500 * (int) $request->input('game_speed'));
        }

        if ($request->filled('fleet_speed') && is_numeric($request->input('fleet_speed'))) {
            $this->settings->write('fleet_speed', 2500 * (int) $request->input('fleet_speed'));
        }

        if ($request->filled('resource_multiplier') && is_numeric($request->input('resource_multiplier'))) {
            $this->settings->write('resource_multiplier', $request->input('resource_multiplier'));
        }

        // Server Access
        $this->settings->write('game_enable', $request->boolean('game_enable') ? 1 : 0);

        if ($request->has('close_reason')) {
            $this->settings->write('close_reason', addslashes((string) $request->input('close_reason')));
        }

        // Date & Time
        if ($request->filled('date_time_zone')) {
            $this->settings->write('date_time_zone', $request->input('date_time_zone'));
        }

        if ($request->filled('date_format')) {
            $this->settings->write('date_format', $request->input('date_format'));
        }

        if ($request->filled('date_format_extended')) {
            $this->settings->write('date_format_extended', $request->input('date_format_extended'));
        }

        // Combat rules
        $this->settings->write('adm_attack', $request->boolean('adm_attack') ? 1 : 0);

        if ($request->filled('fleet_cdr') && is_numeric($request->input('fleet_cdr'))) {
            $this->settings->write('fleet_cdr', max(0, (int) $request->input('fleet_cdr')));
        }

        if ($request->filled('defs_cdr') && is_numeric($request->input('defs_cdr'))) {
            $this->settings->write('defs_cdr', max(0, (int) $request->input('defs_cdr')));
        }

        // Noob protection
        $this->settings->write('noobprotection', $request->boolean('noobprotection') ? 1 : 0);

        if ($request->filled('noobprotectiontime') && is_numeric($request->input('noobprotectiontime'))) {
            $this->settings->write('noobprotectiontime', (int) $request->input('noobprotectiontime'));
        }

        if ($request->filled('noobprotectionmulti') && is_numeric($request->input('noobprotectionmulti'))) {
            $this->settings->write('noobprotectionmulti', (int) $request->input('noobprotectionmulti'));
        }

        return $this->saved('admin.server', 'admin/server.se_all_ok_message');
    }

    private function buildViewData(): array
    {
        return [
            // Identity
            'game_name'            => $this->settings->getString('game_name'),
            'game_logo'            => $this->settings->getString('game_logo'),
            'language_settings'    => Functions::getLanguages($this->settings->getString('lang')),
            'admin_email'          => $this->settings->getString('admin_email'),
            'forum_url'            => $this->settings->getString('forum_url'),
            // Speed & Economy
            'game_speed'           => $this->settings->getInt('game_speed') / 2500,
            'fleet_speed'          => $this->settings->getInt('fleet_speed') / 2500,
            'resource_multiplier'  => $this->settings->getString('resource_multiplier'),
            // Server Access
            'game_enable'          => $this->settings->getBool('game_enable'),
            'close_reason'         => stripslashes($this->settings->getString('close_reason')),
            // Date & Time
            'timezone_options'     => $this->serverSettings->timezoneOptions(),
            'date_format'          => $this->settings->getString('date_format'),
            'date_format_extended' => $this->settings->getString('date_format_extended'),
            // Combat rules
            'adm_attack'           => $this->settings->getBool('adm_attack'),
            'fleet_cdr_options'    => $this->serverSettings->percentageOptions($this->settings->getInt('fleet_cdr')),
            'defs_cdr_options'     => $this->serverSettings->percentageOptions($this->settings->getInt('defs_cdr')),
            // Noob protection
            'noobprotection'       => $this->settings->getBool('noobprotection'),
            'noobprotectiontime'   => $this->settings->getInt('noobprotectiontime'),
            'noobprotectionmulti'  => $this->settings->getInt('noobprotectionmulti'),
        ];
    }
}
