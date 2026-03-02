<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\SettingsService;
use DateTime;
use DateTimeZone;

class ServerSettingsService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * @return array<int, array{group: string, zones: array<int, array{value: string, label: string, selected: bool}>}>
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function timezoneOptions(): array
    {
        $utc = new DateTimeZone('UTC');
        $dt = new DateTime('now', $utc);
        $current = $this->settings->getString('date_time_zone');
        $grouped = [];

        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $tzObj = new DateTimeZone($tz);
            $transitions = $tzObj->getTransitions($dt->getTimestamp(), $dt->getTimestamp());

            foreach ($transitions as $data) {
                $grouped[$data['offset']][] = $tz;
            }
        }

        ksort($grouped);

        $options = [];

        foreach ($grouped as $offset => $zones) {
            $entries = [];

            foreach ($zones as $zone) {
                $entries[] = [
                    'value' => $zone,
                    'label' => $zone,
                    'selected' => $current === $zone,
                ];
            }

            $options[] = [
                'group' => 'GMT' . $this->formatOffset($offset),
                'zones' => $entries,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value: int, label: string, selected: bool}>
     */
    public function percentageOptions(int $current): array
    {
        $options = [];

        for ($i = 0; $i <= 10; $i++) {
            $value = $i * 10;
            $options[] = [
                'value' => $value,
                'label' => $value . '%',
                'selected' => $value === $current,
            ];
        }

        return $options;
    }

    private function formatOffset(int $offset): string
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours >= 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour === 0 && $minutes === 0) {
            $sign = ' ';
        }

        return $sign
            . str_pad((string) $hour, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad((string) $minutes, 2, '0');
    }
}
