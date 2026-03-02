<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StatisticsRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

class StatisticsController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.statistics', $this->buildViewData());
    }

    public function update(StatisticsRequest $request): RedirectResponse
    {
        if ($request->filled('stat_points')) {
            $this->settings->write('stat_points', $request->integer('stat_points'));
        }

        if ($request->filled('stat_update_time')) {
            $this->settings->write('stat_update_time', $request->integer('stat_update_time'));
        }

        if ($request->filled('stat_admin_level')) {
            $this->settings->write('stat_admin_level', $request->integer('stat_admin_level'));
        }

        return $this->saved('admin.statistics', 'admin/statistics.cs_all_ok_message');
    }

    private function buildViewData(): array
    {
        $currentLevel = $this->settings->getInt('stat_admin_level');
        $levelNames = (array) __('admin/global.user_level');
        $ranks = [UserRanks::PLAYER, UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN];

        return [
            'stat_points' => $this->settings->getInt('stat_points'),
            'stat_update_time' => $this->settings->getInt('stat_update_time'),
            'stat_admin_level' => $currentLevel,
            'user_levels' => array_map(fn ($rank) => [
                'id' => $rank,
                'name' => $levelNames[$rank] ?? $rank,
                'selected' => $currentLevel === $rank,
            ], $ranks),
        ];
    }
}
