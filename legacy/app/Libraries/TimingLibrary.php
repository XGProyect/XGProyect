<?php

namespace Xgp\App\Libraries;

abstract class TimingLibrary
{
    public static function setOnlineStatus($onlineTime): string
    {
        $color = 'red';
        $status = __('game/global.offline');

        if ($onlineTime + 60 * 15 >= time()) {
            $color = 'yellow';
            $status = __('game/global.minutes');
        }

        if ($onlineTime + 60 * 10 >= time()) {
            $color = 'lime';
            $status = __('game/global.online');
        }

        return FormatLib::customColor($status, $color);
    }

    /**
     * Format time based on system default extended date config
     *
     * @param string $time Time
     *
     * @return string
     */
    public static function formatExtendedDate($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        return date(Functions::readConfig('date_format_extended'), $time);
    }

    /**
     * Format time based on system default short date config
     *
     * @param string $time Time
     *
     * @return string
     */
    public static function formatShortDate($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        return date(Functions::readConfig('date_format'), $time);
    }

    /**
     * Format time on days format
     *
     * @param string $time Time
     *
     * @return string
     */
    public static function formatDaysTime($time)
    {
        $days = floor((time() - $time) / (3600 * 24));

        return strtr('%s d', ['%s' => $days]);
    }

    /**
     * Get the amount of days left
     *
     * @param int $time
     * @return float
     */
    public static function getDaysLeft(int $time): float
    {
        return (($time - time()) / 24 / 3600);
    }

    /**
     * Get the amount of hours and minutes left
     *
     * @param int $time
     * @return string
     */
    public static function formatHoursMinutesLeft(int $time): string
    {
        return date('h:i', $time - time());
    }
}
