<?php

namespace Xgp\App\Libraries;

use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\TimingLibrary as Timing;

class OfficiersLib
{
    /**
     * isOfficierActive
     *
     * @param int $expire_time Expiration time
     *
     * @return int
     */
    public static function isOfficierActive($expire_time)
    {
        return ($expire_time > time() && $expire_time != 0);
    }

    /**
     * getMaxEspionage
     *
     * @param int $espionage_tech    Espionage tech level
     * @param int $technocrate_level Technocrate level
     *
     * @return int
     */
    public static function getMaxEspionage($espionage_tech, $technocrate_level)
    {
        return $espionage_tech + (1 * (self::isOfficierActive($technocrate_level) ? TECHNOCRATE_SPY : 0));
    }

    /**
     * getMaxComputer
     *
     * @param int $computer_tech Computer tech level
     * @param int $amiral_level  Amiral level
     *
     * @return int
     */
    public static function getMaxComputer($computer_tech, $amiral_level)
    {
        return 1 + $computer_tech + (1 * (self::isOfficierActive($amiral_level) ? AMIRAL : 0));
    }

    public static function getOfficierTimeLeft(int $expiration): string
    {
        $langLine = 'of_time_remaining_many';
        $timeLeft = strtr(
            Format::prettyTimeAgo(Timing::formatShortDate($expiration)),
            __('game/global.timing')
        );

        if (Timing::getDaysLeft($expiration) <= 1) {
            $langLine = 'of_time_remaining_less';
            $timeLeft = Timing::formatHoursMinutesLeft($expiration);
        }

        if (Timing::getDaysLeft($expiration) > 1 && Timing::getDaysLeft($expiration) < 2) {
            $langLine = 'of_time_remaining_one';
            $timeLeft = '';
        }

        return StringsHelper::parseReplacements(
            __('game/officier.' . $langLine),
            [$timeLeft]
        );
    }
}
