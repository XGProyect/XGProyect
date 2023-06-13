<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use JsonException;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Adm\Home;

class HomeController extends BaseController
{
    private Home $homeModel;
    private array $user;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->homeModel = new Home();
        $this->user = Users::getInstance()->getUserData();

        $userStats = $this->homeModel->getUsersStats();

        Template::legacyView(
            'admin.home',
            array_merge(
                $userStats,
                $this->buildAlertsBlock(),
                [
                    'numberUsers' => Format::prettyNumber((int) $userStats['number_users']),
                    'numberAlliances' => Format::prettyNumber((int) $userStats['number_alliances']),
                    'numberPlanets' => Format::prettyNumber((int) $userStats['number_planets']),
                    'numberMoons' => Format::prettyNumber((int) $userStats['number_moons']),
                    'numberFleets' => Format::prettyNumber((int) $userStats['number_fleets']),
                    'numberReports' => Format::prettyNumber((int) $userStats['number_reports']),
                    'averageUserPoints' => Format::shortlyNumber((int) $userStats['average_user_points']),
                    'averageAlliancePoints' => Format::shortlyNumber((int) $userStats['average_alliance_points']),

                    'databaseSize' => Format::prettyBytes($this->homeModel->getDbSize()['db_size']),
                    'databaseServer' => $this->homeModel->getDbVersion(),
                    'phpVersion' => PHP_VERSION,
                    'serverVersion' => config('version.files'),
                ]
            )
        );
    }

    private function buildAlertsBlock(): array
    {
        $alert = [];

        if ($this->user['user_authlevel'] >= 3) {
            if ((bool) (@fileperms(CONFIGS_PATH . 'xgp-db-config.php') & 0x0002)) {
                $alert[] = __('admin/home.hm_config_file_writable');
            }

            if ($this->getServerErrors()) {
                $alert[] = __('admin/home.hm_errors');
            }

            if ($this->checkUpdates()) {
                $alert[] = __('admin/home.hm_old_version');
            }

            if (Administration::installDirExists()) {
                $alert[] = __('admin/home.hm_install_file_detected');
            }

            if (Functions::readConfig('version') != config('version.files')) {
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
                    $systemVersion = Functions::readConfig('version');
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
}
