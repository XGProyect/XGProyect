<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services;

use App\Services\TimingService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class TimingServiceTest extends TestCase
{
    private TimingService $service;

    protected function setUp(): void
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('getString')->willReturnMap([
            ['date_format_extended', 'Y-m-d H:i:s'],
            ['date_format', 'Y-m-d'],
        ]);

        $this->service = new TimingService($settings);
    }

    public function testFormatExtendedDateFromTimestamp(): void
    {
        $timestamp = mktime(14, 30, 0, 6, 15, 2025);
        $result = $this->service->formatExtendedDate($timestamp);
        $this->assertEquals('2025-06-15 14:30:00', $result);
    }

    public function testFormatExtendedDateFromString(): void
    {
        $result = $this->service->formatExtendedDate('2025-06-15 14:30:00');
        $this->assertEquals('2025-06-15 14:30:00', $result);
    }

    public function testFormatShortDateFromTimestamp(): void
    {
        $timestamp = mktime(14, 30, 0, 6, 15, 2025);
        $result = $this->service->formatShortDate($timestamp);
        $this->assertEquals('2025-06-15', $result);
    }

    public function testFormatShortDateFromString(): void
    {
        $result = $this->service->formatShortDate('2025-06-15 14:30:00');
        $this->assertEquals('2025-06-15', $result);
    }

    public function testFormatDaysElapsed(): void
    {
        // 2 days = 172800 seconds
        $this->assertEquals('2 d', $this->service->formatDaysElapsed(0, 172800));
    }

    public function testFormatDaysElapsedSameDay(): void
    {
        $this->assertEquals('0 d', $this->service->formatDaysElapsed(1000, 1500));
    }

    public function testGetDaysLeft(): void
    {
        // 172800 seconds ahead = 2 days
        $this->assertEquals(2.0, $this->service->getDaysLeft(272800, 100000));
    }

    public function testGetDaysLeftExpired(): void
    {
        $result = $this->service->getDaysLeft(100000, 272800);
        $this->assertLessThan(0, $result);
    }

    public function testFormatHoursMinutesLeft(): void
    {
        // 3661 seconds left = 1h 1m
        $result = $this->service->formatHoursMinutesLeft(13661, 10000);
        $this->assertEquals('01:01', $result);
    }

    public function testFormatHoursMinutesLeftExpired(): void
    {
        $this->assertEquals('00:00', $this->service->formatHoursMinutesLeft(1000, 2000));
    }

    public function testGetOnlineStatusOnline(): void
    {
        $this->assertEquals('online', $this->service->getOnlineStatus(1000, 1500));
    }

    public function testGetOnlineStatusIdle(): void
    {
        // 10min < elapsed <= 15min
        $this->assertEquals('idle', $this->service->getOnlineStatus(1000, 1700));
    }

    public function testGetOnlineStatusOffline(): void
    {
        // More than 15 minutes
        $this->assertEquals('offline', $this->service->getOnlineStatus(1000, 2901));
    }

    public function testGetOnlineStatusBoundaryAt10Min(): void
    {
        // Exactly 600 seconds (10 min) → still online
        $this->assertEquals('online', $this->service->getOnlineStatus(1000, 1600));
    }

    public function testGetOnlineStatusBoundaryAt15Min(): void
    {
        // Exactly 900 seconds (15 min) → still idle
        $this->assertEquals('idle', $this->service->getOnlineStatus(1000, 1900));
    }
}
