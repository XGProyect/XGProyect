<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\SecurePageLib;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\UpdatesLibrary;

// require some stuff
require_once XGP_ROOT . 'config' . DIRECTORY_SEPARATOR . 'constants.php';

class Common
{
    private const APPLICATIONS = [
        'admin' => ['setSystemTimezone', 'setSecure'],
        'game' => ['setSystemTimezone', 'setSecure', 'setUpdates', 'isServerOpen', 'checkBanStatus'],
    ];
    private bool $is_installed = false;

    public function bootUp(string $app): void
    {
        // overall loads
        $this->isServerInstalled();

        // specific pages load or executions
        if (isset(self::APPLICATIONS[$app])) {
            foreach (self::APPLICATIONS[$app] as $method) {
                $this->$method();
            }
        }
    }

    private function isServerInstalled(): void
    {
        try {
            $config_file = CONFIGS_PATH . 'xgp-db-config.php';

            if (file_exists($config_file)) {
                require $config_file;

                // check if it is installed
                if (config('DB_HOST') && config('DB_PORT') && config('DB_USERNAME') && config('DB_PASSWORD') && config('DB_DATABASE') && config('DB_PREFIX')) {
                    $this->is_installed = true;
                }
            } else {
                fopen($config_file, 'w+');
            }

            // set language
            Functions::setLanguage();

            if (!$this->is_installed && !defined('IN_INSTALL')) {
                Functions::redirect(SYSTEM_ROOT . 'install.php');
            }
        } catch (Exception $e) {
            die('Error #0001' . $e->getMessage());
        }
    }

    private function setSystemTimezone(): void
    {
        date_default_timezone_set(Options::getInstance()->get('date_time_zone'));
    }

    private function setSecure(): void
    {
        $current_page = isset($_GET['page']) ? $_GET['page'] : '';

        $exclude = ['languages'];

        if (!in_array($current_page, $exclude)) {
            SecurePageLib::run();
        }
    }

    private function setUpdates(): void
    {
        define('SHIP_DEBRIS_FACTOR', Options::getInstance()->get('fleet_cdr') / 100);
        define('DEFENSE_DEBRIS_FACTOR', Options::getInstance()->get('defs_cdr') / 100);

        // Several updates
        new UpdatesLibrary();
    }

    private function isServerOpen(): void
    {
        if (Options::getInstance()->get('game_enable') == SwitchInt::off) {
            /** @var User $user */
            $user = Auth::getUser();

            if ($user->authlevel < UserRanks::ADMIN) {
                Functions::popupMessage(Options::getInstance()->get('close_reason'));
            }
        }
    }

    private function checkBanStatus(): void
    {
        /** @var User $user */
        $user = Auth::getUser();

        if ($user->ban !== null && $user->ban->until > time()) {
            Functions::popupMessage(
                StringsHelper::parseReplacements(
                    __('game/global.bg_banned'),
                    [Timing::formatExtendedDate($user->ban->until)]
                )
            );
        }
    }
}
