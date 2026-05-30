<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Models\User;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\SecurePageLib;
use Xgp\App\Libraries\UpdatesLibrary;

// require some stuff
require_once XGP_ROOT . 'config' . DIRECTORY_SEPARATOR . 'constants.php';

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Common
{
    private const APPLICATIONS = [
        'game' => ['setSystemTimezone', 'setSecure', 'setUpdates', 'isServerOpen', 'checkBanStatus'],
    ];

    public function bootUp(string $app): void
    {
        // overall loads
        Functions::setLanguage();

        // specific pages load or executions
        if (isset(self::APPLICATIONS[$app])) {
            foreach (self::APPLICATIONS[$app] as $method) {
                $this->$method();
            }
        }
    }

    private function setSystemTimezone(): void
    {
        date_default_timezone_set(app(SettingsService::class)->getString('date_time_zone'));
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
        $settings = app(SettingsService::class);
        define('SHIP_DEBRIS_FACTOR', $settings->getInt('fleet_cdr') / 100);
        define('DEFENSE_DEBRIS_FACTOR', $settings->getInt('defs_cdr') / 100);

        // Several updates - use DI container to resolve dependencies
        app(UpdatesLibrary::class);
    }

    private function isServerOpen(): void
    {
        $settings = app(SettingsService::class);
        if ($settings->getInt('game_enable') == SwitchInt::off) {
            /** @var User $user */
            $user = Auth::getUser();

            if ($user->authlevel < UserRanks::ADMIN) {
                Functions::popupMessage($settings->getString('close_reason'));
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
                    [app(TimingService::class)->formatExtendedDate($user->ban->until)]
                )
            );
        }
    }
}
