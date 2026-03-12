<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Alliance;
use App\Models\AllianceStatistics;
use App\Models\Fleets;
use App\Models\Planets;
use App\Models\Reports;
use App\Models\User;
use App\Models\UsersStatistics;
use App\Services\Admin\HomeService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Libraries\FormatLib as Format;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class HomeController extends AdminSettingsController
{
    public function __construct(
        SettingsService $settings,
        private readonly HomeService $homeService,
    ) {
        parent::__construct($settings);
    }

    public function index(): View
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        return $this->view('admin.home', [
            ...$this->buildAlertsBlock($authUser),
            'numberUsers' => Format::prettyNumber(User::count()),
            'numberAlliances' => Format::prettyNumber(Alliance::count()),
            'numberPlanets' => Format::prettyNumber(Planets::where('planet_type', 1)->count()),
            'numberMoons' => Format::prettyNumber(Planets::where('planet_type', 3)->count()),
            'numberFleets' => Format::prettyNumber(Fleets::count()),
            'numberReports' => Format::prettyNumber(Reports::count()),
            'averageUserPoints' => Format::shortlyNumber((int) UsersStatistics::avg('user_statistic_total_points')),
            'averageAlliancePoints' => Format::shortlyNumber((int) AllianceStatistics::avg('alliance_statistic_total_points')),
            'databaseSize' => $this->homeService->getDbSize(),
            'databaseServer' => $this->homeService->getDbVersion(),
            'phpVersion' => PHP_VERSION,
            'serverVersion' => config('version.files'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAlertsBlock(User $authUser): array
    {
        $alerts = $authUser->authlevel >= UserRanks::ADMIN
            ? array_filter([
                $this->homeService->isConfigFileWorldWritable() ? (string) __('admin/home.hm_config_file_writable') : null,
                $this->homeService->hasServerErrors() ? (string) __('admin/home.hm_errors') : null,
                $this->homeService->isVersionOutdated() ? (string) __('admin/home.hm_old_version') : null,
                $this->homeService->isInstallFilePresent() ? (string) __('admin/home.hm_install_file_detected') : null,
                $this->homeService->hasPendingUpdate() ? (string) __('admin/home.hm_update_required') : null,
            ])
            : [];

        $count = count($alerts);

        return match (true) {
            $count > 1 => [
                'errorMessage' => implode('<br>', $alerts),
                'secondStyle' => 'alert-danger',
                'errorType' => __('admin/home.hm_error'),
            ],
            $count === 1 => [
                'errorMessage' => implode('<br>', $alerts),
                'secondStyle' => 'alert-warning',
                'errorType' => __('admin/home.hm_warning'),
            ],
            default => [
                'errorMessage' => __('admin/home.hm_all_ok'),
                'secondStyle' => 'alert-success',
                'errorType' => __('admin/home.hm_ok'),
            ],
        };
    }
}
