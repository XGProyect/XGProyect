<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Services\Game\Formulas\NoobProtectionService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class NoobProtectionServiceTest extends TestCase
{
    private function createService(bool $protection = true, int $time = 5000, int $multi = 5, int $adminLevel = 3): NoobProtectionService
    {
        $settings = $this->createMock(SettingsService::class);

        $settings->method('getBool')->willReturnMap([
            ['noobprotection', $protection],
        ]);

        $settings->method('getInt')->willReturnMap([
            ['noobprotectiontime', $time],
            ['noobprotectionmulti', $multi],
            ['stat_admin_level', $adminLevel],
        ]);

        return new NoobProtectionService($settings);
    }

    public function testIsWeakWhenStrongerPlayer(): void
    {
        $service = $this->createService();
        // 50000 > 1000 * 5 → true, 1000 < 5000 → not exempt → weak
        $this->assertTrue($service->isWeak(50000, 1000));
    }

    public function testIsWeakWhenEqualStrength(): void
    {
        $service = $this->createService();
        // 5000 > 1000 * 5 → false
        $this->assertFalse($service->isWeak(5000, 1000));
    }

    public function testIsWeakExemptByTime(): void
    {
        $service = $this->createService(time: 5000);
        // 50000 > 6000 * 5 → true, but 6000 > 5000 → exempt
        $this->assertFalse($service->isWeak(50000, 6000));
    }

    public function testIsWeakProtectionDisabled(): void
    {
        $service = $this->createService(protection: false);
        $this->assertFalse($service->isWeak(50000, 1000));
    }

    public function testIsStrongWhenWeakerPlayer(): void
    {
        $service = $this->createService();
        // 1000 * 5 < 50000 → true, 1000 < 5000 → not exempt → strong
        $this->assertTrue($service->isStrong(1000, 50000));
    }

    public function testIsStrongWhenEqualStrength(): void
    {
        $service = $this->createService();
        // 1000 * 5 < 5000 → false
        $this->assertFalse($service->isStrong(1000, 5000));
    }

    public function testIsStrongExemptByTime(): void
    {
        $service = $this->createService(time: 5000);
        // 6000 * 5 < 50000 → true, but 6000 > 5000 → exempt
        $this->assertFalse($service->isStrong(6000, 50000));
    }

    public function testIsStrongProtectionDisabled(): void
    {
        $service = $this->createService(protection: false);
        $this->assertFalse($service->isStrong(1000, 50000));
    }

    public function testIsRankVisibleAllowed(): void
    {
        $service = $this->createService(adminLevel: 3);
        $this->assertTrue($service->isRankVisible(1));
        $this->assertTrue($service->isRankVisible(3));
    }

    public function testIsRankVisibleNotAllowed(): void
    {
        $service = $this->createService(adminLevel: 3);
        $this->assertFalse($service->isRankVisible(4));
    }

    public function testIsWeakWithZeroMultiDefaultsToOne(): void
    {
        $service = $this->createService(multi: 0);
        // multi forced to 1: 50000 > 1000 * 1 → weak
        $this->assertTrue($service->isWeak(50000, 1000));
    }

    public function testIsStrongWithZeroProtectionTime(): void
    {
        $service = $this->createService(time: 0);
        // 1000 * 5 < 50000 → true, protectionTime=0 → time check skipped → strong
        $this->assertTrue($service->isStrong(1000, 50000));
    }
}
