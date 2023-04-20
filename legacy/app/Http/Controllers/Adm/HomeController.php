<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use JsonException;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;
use Xgp\App\Models\Adm\Home;

class HomeController extends BaseController
{
    private Home $homeModel;

    public function __invoke(): void
    {
        Administration::checkSession();


        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage(__('admin.global.no_permissions')));
        }

        $this->homeModel = new Home();

        // build the page
        $this->buildPage();
    }

    private function buildPage(): void
    {
        $server_stats = $this->homeModel->getUsersStats();

        Page::getInstance()->displayAdmin(
            Template::getInstance()->render(
                'admin.home_view',
                array_merge(
                    $server_stats,
                    [
                        'alert' => [$this->buildAlertsBlock()],
                        'average_user_points' => Format::shortlyNumber($server_stats['average_user_points']),
                        'average_alliance_points' => Format::shortlyNumber($server_stats['average_alliance_points']),
                        'database_size' => Format::prettyBytes($this->homeModel->getDbSize()['db_size']),
                        'database_server' => $this->homeModel->getDbVersion(),
                        'php_version' => PHP_VERSION,
                        'server_version' => SYSTEM_VERSION,
                    ]
                )
            )
        );
    }

    /**
     * Build the alerts block based on our current server status
     *
     * @return array
     */
    private function buildAlertsBlock(): array
    {
        $alert = [];

        if ($this->user['user_authlevel'] >= 3) {
            if ((bool) (@fileperms(CONFIGS_PATH . 'xgp-db-config.php') & 0x0002)) {
                $alert[] = $this->langs->line('hm_config_file_writable');
            }

            if ($this->getServerErrors()) {
                $alert[] = $this->langs->line('hm_errors');
            }

            if ($this->checkUpdates()) {
                $alert[] = $this->langs->line('hm_old_version');
            }

            if (Administration::installDirExists()) {
                $alert[] = $this->langs->line('hm_install_file_detected');
            }

            if (Functions::readConfig('version') != SYSTEM_VERSION) {
                $alert[] = $this->langs->line('hm_update_required');
            }
        }

        $alerts_count = count($alert);
        $messages = $second_style = $error_type = null;

        if ($alerts_count > 1) {
            $messages = join('<br>', $alert);
            $second_style = 'alert-danger';
            $error_type = $this->langs->line('hm_error');
        }

        if ($alerts_count == 1) {
            $messages = join('<br>', $alert);
            $second_style = 'alert-warning';
            $error_type = $this->langs->line('hm_warning');
        }

        return [
            'error_message' => $messages ?? $this->langs->line('hm_all_ok'),
            'second_style' => $second_style ?? 'alert-success',
            'error_type' => $error_type ?? $this->langs->line('hm_ok'),
        ];
    }

    /**
     * Check if there's any new version available
     *
     * @return boolean
     */
    private function checkUpdates(): bool
    {
        try {
            if (function_exists('file_get_contents')) {
                $file_data = @file_get_contents(
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

                if ($file_data) {
                    $system_v = Functions::readConfig('version');
                    $last_v = @json_decode(
                        $file_data,
                        false,
                        512,
                        JSON_THROW_ON_ERROR
                    )->version;

                    return version_compare($system_v, $last_v, '<');
                }
            }

            return false;
        } catch (JsonException $e) {
            return false;
        }
    }

    private function getServerErrors(): bool
    {
        return (count(glob(LOGS_PATH . '*.txt')) > 0);
    }
}
