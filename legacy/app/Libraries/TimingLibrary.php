<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use App\Services\SettingsService;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
abstract class TimingLibrary
{
    public static function setOnlineStatus(int $onlineTime): string
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

    public static function formatExtendedDate(string | int $time): string
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        return date(app(SettingsService::class)->getString('date_format_extended'), (int) $time);
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

        return date(app(SettingsService::class)->getString('date_format'), $time);
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
     *
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
     *
     * @return string
     */
    public static function formatHoursMinutesLeft(int $time): string
    {
        return date('h:i', $time - time());
    }
}
