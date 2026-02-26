<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use JsonException;
use stdClass;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Users;

class HomeController extends BaseController
{
    /** @var array<string, mixed> $user */
    private array $user;
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->user = Users::getInstance()->getUserData();
        $userStats = $this->getUsersStats();

        Template::legacyView(
            'admin.home',
            array_merge(
                $this->buildAlertsBlock(),
                [
                    'numberUsers' => Format::prettyNumber((int) $userStats->number_users),
                    'numberAlliances' => Format::prettyNumber((int) $userStats->number_alliances),
                    'numberPlanets' => Format::prettyNumber((int) $userStats->number_planets),
                    'numberMoons' => Format::prettyNumber((int) $userStats->number_moons),
                    'numberFleets' => Format::prettyNumber((int) $userStats->number_fleets),
                    'numberReports' => Format::prettyNumber((int) $userStats->number_reports),
                    'averageUserPoints' => Format::shortlyNumber((int) $userStats->average_user_points),
                    'averageAlliancePoints' => Format::shortlyNumber((int) $userStats->average_alliance_points),
                    'databaseSize' => $this->getDbSize(),
                    'databaseServer' => $this->getDbVersion(),
                    'phpVersion' => PHP_VERSION,
                    'serverVersion' => config('version.files'),
                ]
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAlertsBlock(): array
    {
        $alert = [];

        if ($this->user['authlevel'] >= 3) {
            if ((bool) (@fileperms(CONFIGS_PATH . 'xgp-db-config.php') & 0x0002)) {
                $alert[] = __('admin/home.hm_config_file_writable');
            }

            if ($this->getServerErrors()) {
                $alert[] = __('admin/home.hm_errors');
            }

            if ($this->checkUpdates()) {
                $alert[] = __('admin/home.hm_old_version');
            }

            if ($this->installDirExists()) {
                $alert[] = __('admin/home.hm_install_file_detected');
            }

            if (Options::getInstance()->get('version') != config('version.files')) {
                $alert[] = __('admin/home.hm_update_required');
            }
        }

        $alerts_count = count($alert);
        $messages = $secondStyle = $errorType = null;

        if ($alerts_count > 1) {
            $messages = join('<br>', $alert);
            $secondStyle = 'alert-danger';
            $errorType = __('admin/home.hm_error');
        }

        if ($alerts_count == 1) {
            $messages = join('<br>', $alert);
            $secondStyle = 'alert-warning';
            $errorType = __('admin/home.hm_warning');
        }

        return [
            'errorMessage' => $messages ?? __('admin/home.hm_all_ok'),
            'secondStyle' => $secondStyle ?? 'alert-success',
            'errorType' => $errorType ?? __('admin/home.hm_ok'),
        ];
    }

    private function checkUpdates(): bool
    {
        try {
            if (function_exists('file_get_contents')) {
                $fileData = @file_get_contents(
                    'https://updates.xgproyect.org/latest.php',
                    false,
                    stream_context_create(
                        ['https' =>
                            [
                                'timeout' => 1, // one second
                            ],
                        ]
                    )
                );

                if ($fileData) {
                    $systemVersion = Options::getInstance()->get('version');
                    $lastestVersion = @json_decode(
                        $fileData,
                        false,
                        512,
                        JSON_THROW_ON_ERROR
                    )->version;

                    return version_compare($systemVersion, $lastestVersion, '<');
                }
            }

            return false;
        } catch (JsonException $e) {
            return false;
        }
    }

    private function getServerErrors(): bool
    {
        return (count(glob(storage_path('logs') . '/xgproyect.log')) > 0);
    }

    private function getUsersStats(): stdClass
    {
        return DB::selectOne(
            'SELECT
                (
                    SELECT
                        COUNT(u.`id`) AS `total_users`
                    FROM
                        `' . config('DB_PREFIX') . 'users` u
                ) AS `number_users`,
                (
                    SELECT
                        COUNT(a.`alliance_id`) AS `total_alliances`
                    FROM
                        `' . config('DB_PREFIX') . 'alliance` a
                ) AS `number_alliances`,
                (
                    SELECT
                        COUNT(p.`planet_id`) AS `total_planets`
                    FROM
                        `' . config('DB_PREFIX') . 'planets` p
                    WHERE
                        p.`planet_type` = "1"
                ) AS `number_planets`,
                (
                    SELECT
                        COUNT(m.`planet_id`) AS `total_moons`
                    FROM
                        `' . config('DB_PREFIX') . 'planets` m
                    WHERE
                        m.`planet_type` = "3"
                ) AS `number_moons`,
                (
                    SELECT
                        COUNT(f.`fleet_id`) AS `total_fleets`
                    FROM
                        `' . config('DB_PREFIX') . 'fleets` f
                ) AS `number_fleets`,
                (
                    SELECT
                        COUNT(r.`report_rid`) AS `total_reports`
                    FROM
                        `' . config('DB_PREFIX') . 'reports` r
                ) AS `number_reports`,
                (
                    SELECT
                        FLOOR(AVG(s.`user_statistic_total_points`)) AS `average_user_total_points`
                    FROM
                        `' . config('DB_PREFIX') . 'users_statistics` s
                ) AS `average_user_points`,
                (
                    SELECT
                        FLOOR(AVG(s.`alliance_statistic_total_points`)) AS `average_alliance_total_points`
                    FROM
                        `' . config('DB_PREFIX') . 'alliance_statistics` s
                ) AS `average_alliance_points`'
        );
    }

    private function installDirExists(): bool
    {
        return (file_exists(PUBLIC_PATH . 'install.php'));
    }

    private function getDbVersion(): string
    {
        return DB::selectOne('SHOW VARIABLES LIKE "version"')->Value;
    }

    private function getDbSize(): string
    {
        return Format::prettyBytes(
            (int) DB::selectOne(
                "SELECT
                    SUM(data_length + index_length) AS 'db_size'
                FROM information_schema.TABLES
                WHERE table_schema = :table_schema;",
                [
                    'table_schema' => config('DB_DATABASE')
                ]
            )->db_size
        );
    }
}
