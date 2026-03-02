<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use DateTime;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Functions;

class ServerController extends BaseController
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

        Template::legacyView('admin.server', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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

        return redirect()->route('admin.server')
            ->with('success', __('admin/server.se_all_ok_message'));
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
            'timezone_options'     => $this->buildTimezoneOptions(),
            'date_format'          => $this->settings->getString('date_format'),
            'date_format_extended' => $this->settings->getString('date_format_extended'),
            // Combat rules
            'adm_attack'           => $this->settings->getBool('adm_attack'),
            'fleet_cdr_options'    => $this->buildPercentageOptions($this->settings->getInt('fleet_cdr')),
            'defs_cdr_options'     => $this->buildPercentageOptions($this->settings->getInt('defs_cdr')),
            // Noob protection
            'noobprotection'       => $this->settings->getBool('noobprotection'),
            'noobprotectiontime'   => $this->settings->getInt('noobprotectiontime'),
            'noobprotectionmulti'  => $this->settings->getInt('noobprotectionmulti'),
        ];
    }

    private function buildTimezoneOptions(): array
    {
        $utc = new DateTimeZone('UTC');
        $dt = new DateTime('now', $utc);
        $current = $this->settings->getString('date_time_zone');
        $grouped = [];

        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $tzObj = new DateTimeZone($tz);
            $transitions = $tzObj->getTransitions($dt->getTimestamp(), $dt->getTimestamp());

            foreach ($transitions as $data) {
                $grouped[$data['offset']][] = $tz;
            }
        }

        ksort($grouped);

        $options = [];

        foreach ($grouped as $offset => $zones) {
            $label = 'GMT' . $this->formatOffset($offset);
            $entries = [];

            foreach ($zones as $zone) {
                $entries[] = [
                    'value'    => $zone,
                    'label'    => $zone,
                    'selected' => $current === $zone,
                ];
            }

            $options[] = ['group' => $label, 'zones' => $entries];
        }

        return $options;
    }

    private function formatOffset(int $offset): string
    {
        $hours     = $offset / 3600;
        $remainder = $offset % 3600;
        $sign      = $hours >= 0 ? '+' : '-';
        $hour      = (int) abs($hours);
        $minutes   = (int) abs($remainder / 60);

        if ($hour === 0 && $minutes === 0) {
            $sign = ' ';
        }

        return $sign . str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $minutes, 2, '0');
    }

    private function buildPercentageOptions(int $current): array
    {
        $options = [];

        for ($i = 0; $i <= 10; $i++) {
            $value     = $i * 10;
            $options[] = [
                'value'    => $value,
                'label'    => $value . '%',
                'selected' => $value === $current,
            ];
        }

        return $options;
    }
}
