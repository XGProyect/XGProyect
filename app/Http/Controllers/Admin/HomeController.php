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
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Libraries\FormatLib as Format;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class HomeController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        return $this->view('admin.home', [
            ...$this->buildAlertsBlock($authUser),
            'numberUsers' => Format::prettyNumber(User::count()), // @phpstan-ignore staticMethod.notFound
            'numberAlliances' => Format::prettyNumber(Alliance::count()), // @phpstan-ignore staticMethod.notFound
            'numberPlanets' => Format::prettyNumber(Planets::where('planet_type', 1)->count()), // @phpstan-ignore staticMethod.notFound
            'numberMoons' => Format::prettyNumber(Planets::where('planet_type', 3)->count()), // @phpstan-ignore staticMethod.notFound
            'numberFleets' => Format::prettyNumber(Fleets::count()), // @phpstan-ignore staticMethod.notFound
            'numberReports' => Format::prettyNumber(Reports::count()), // @phpstan-ignore staticMethod.notFound
            'averageUserPoints' => Format::shortlyNumber((int) UsersStatistics::avg('user_statistic_total_points')), // @phpstan-ignore staticMethod.notFound
            'averageAlliancePoints' => Format::shortlyNumber((int) AllianceStatistics::avg('alliance_statistic_total_points')), // @phpstan-ignore staticMethod.notFound
            'databaseSize' => $this->getDbSize(),
            'databaseServer' => $this->getDbVersion(),
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
                $this->configFileWritableAlert(),
                $this->serverErrorsAlert(),
                $this->outdatedVersionAlert(),
                $this->installFileAlert(),
                $this->pendingUpdateAlert(),
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

    private function configFileWritableAlert(): ?string
    {
        $file = config_path('xgp-db-config.php');

        return file_exists($file) && (fileperms($file) & 0x0002) !== 0
            ? (string) __('admin/home.hm_config_file_writable') // @phpstan-ignore cast.string
            : null;
    }

    private function serverErrorsAlert(): ?string
    {
        return $this->getServerErrors() ? (string) __('admin/home.hm_errors') : null; // @phpstan-ignore cast.string
    }

    private function outdatedVersionAlert(): ?string
    {
        return $this->checkUpdates() ? (string) __('admin/home.hm_old_version') : null; // @phpstan-ignore cast.string
    }

    private function installFileAlert(): ?string
    {
        return $this->installDirExists() ? (string) __('admin/home.hm_install_file_detected') : null; // @phpstan-ignore cast.string
    }

    private function pendingUpdateAlert(): ?string
    {
        return $this->settings->getString('version') !== config('version.files')
            ? (string) __('admin/home.hm_update_required') // @phpstan-ignore cast.string
            : null;
    }

    private function checkUpdates(): bool
    {
        try {
            $response = Http::timeout(1)->get('https://updates.xgproyect.org/latest.json');

            if ($response->successful()) {
                $latestVersion = $response->json('version');

                return is_string($latestVersion) &&
                    version_compare($this->settings->getString('version'), $latestVersion, '<');
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    private function getServerErrors(): bool
    {
        return file_exists(storage_path('logs/xgproyect.log'));
    }

    private function installDirExists(): bool
    {
        return file_exists(public_path('install.php'));
    }

    private function getDbVersion(): string
    {
        return (string) DB::scalar('SELECT @@version'); // @phpstan-ignore cast.string
    }

    private function getDbSize(): string
    {
        return Format::prettyBytes(
            // @phpstan-ignore-next-line cast.int
            (int) DB::scalar(
                'SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = ?',
                [DB::getDatabaseName()]
            )
        );
    }
}
