<?php

declare(strict_types=1);

namespace App\Services;

class TimingService
{
    public function __construct(
        private SettingsService $settings,
    ) {
    }

    public function formatExtendedDate(string | int $time): string
    {
        if (!is_numeric($time)) {
            $time = strtotime((string) $time);
        }

        return date($this->settings->getString('date_format_extended'), (int) $time);
    }

    public function formatShortDate(string | int $time): string
    {
        if (!is_numeric($time)) {
            $time = strtotime((string) $time);
        }

        return date($this->settings->getString('date_format'), (int) $time);
    }

    public function formatDaysElapsed(int $timestamp, int $currentTime): string
    {
        $days = (int) floor(($currentTime - $timestamp) / 86400);

        return $days . ' d';
    }

    public function getDaysLeft(int $expireTime, int $currentTime): float
    {
        return ($expireTime - $currentTime) / 86400;
    }

    public function formatHoursMinutesLeft(int $expireTime, int $currentTime): string
    {
        $remaining = $expireTime - $currentTime;

        if ($remaining <= 0) {
            return '00:00';
        }

        return date('H:i', $remaining);
    }

    public function getOnlineStatus(int $onlineTime, int $currentTime): string
    {
        if ($onlineTime + 600 >= $currentTime) {
            return 'online';
        }

        if ($onlineTime + 900 >= $currentTime) {
            return 'idle';
        }

        return 'offline';
    }
}
