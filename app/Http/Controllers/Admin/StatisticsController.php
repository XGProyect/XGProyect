<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

class StatisticsController extends AdminSettingsController
{
    public function __construct(AdministrationService $administrationService, SettingsService $settings)
    {
        parent::__construct($administrationService, $settings);
    }

    public function index(): View
    {
        $this->authorize();

        return $this->view('admin.statistics', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize();

        if ($request->filled('stat_points') && is_numeric($request->input('stat_points'))) {
            $value = (int) $request->input('stat_points');

            if ($value >= 1) {
                $this->settings->write('stat_points', $value);
            }
        }

        if ($request->filled('stat_update_time') && is_numeric($request->input('stat_update_time'))) {
            $value = (int) $request->input('stat_update_time');

            if ($value >= 1) {
                $this->settings->write('stat_update_time', $value);
            }
        }

        if ($request->filled('stat_admin_level') && is_numeric($request->input('stat_admin_level'))) {
            $value = (int) $request->input('stat_admin_level');

            if ($value >= UserRanks::PLAYER && $value <= UserRanks::ADMIN) {
                $this->settings->write('stat_admin_level', $value);
            }
        }

        return $this->saved('admin.statistics', 'admin/statistics.cs_all_ok_message');
    }

    private function buildViewData(): array
    {
        $currentLevel = $this->settings->getInt('stat_admin_level');
        $levelNames   = (array) __('admin/global.user_level');
        $ranks        = [UserRanks::PLAYER, UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN];

        return [
            'stat_points'      => $this->settings->getInt('stat_points'),
            'stat_update_time' => $this->settings->getInt('stat_update_time'),
            'stat_admin_level' => $currentLevel,
            'user_levels'      => array_map(fn ($rank) => [
                'id'       => $rank,
                'name'     => $levelNames[$rank] ?? $rank,
                'selected' => $currentLevel === $rank,
            ], $ranks),
        ];
    }
}
