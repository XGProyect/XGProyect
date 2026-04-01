<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Services\Game\Formulas\OfficerService;
use PHPUnit\Framework\TestCase;

class OfficerServiceTest extends TestCase
{
    private OfficerService $service;

    protected function setUp(): void
    {
        if (!defined('TECHNOCRATE_SPY')) {
            define('TECHNOCRATE_SPY', 2);
        }

        if (!defined('AMIRAL')) {
            define('AMIRAL', 2);
        }

        $this->service = new OfficerService();
    }

    public function testIsOfficerActiveTrue(): void
    {
        $this->assertTrue($this->service->isOfficerActive(2000, 1000));
    }

    public function testIsOfficerActiveFalse(): void
    {
        $this->assertFalse($this->service->isOfficerActive(1000, 2000));
    }

    public function testIsOfficerActiveExactlyEqual(): void
    {
        $this->assertFalse($this->service->isOfficerActive(1000, 1000));
    }

    public function testGetMaxEspionageWithTechnocrate(): void
    {
        // 5 + 2 = 7
        $this->assertEquals(7, $this->service->getMaxEspionage(5, true));
    }

    public function testGetMaxEspionageWithoutTechnocrate(): void
    {
        $this->assertEquals(5, $this->service->getMaxEspionage(5, false));
    }

    public function testGetMaxComputerWithAdmiral(): void
    {
        // 1 + 5 + 2 = 8
        $this->assertEquals(8, $this->service->getMaxComputer(5, true));
    }

    public function testGetMaxComputerWithoutAdmiral(): void
    {
        // 1 + 5 + 0 = 6
        $this->assertEquals(6, $this->service->getMaxComputer(5, false));
    }

    public function testGetMaxComputerZeroTech(): void
    {
        // 1 + 0 + 0 = 1
        $this->assertEquals(1, $this->service->getMaxComputer(0, false));
    }

    public function testGetDaysLeftPositive(): void
    {
        // 172800 seconds = 2 days
        $this->assertEquals(2.0, $this->service->getDaysLeft(272800, 100000));
    }

    public function testGetDaysLeftNegative(): void
    {
        // Already expired
        $result = $this->service->getDaysLeft(100000, 272800);
        $this->assertLessThan(0, $result);
    }
}
