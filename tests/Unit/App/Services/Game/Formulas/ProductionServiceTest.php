<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Services\Game\Formulas\ProductionService;
use PHPUnit\Framework\TestCase;

class ProductionServiceTest extends TestCase
{
    private ProductionService $service;

    protected function setUp(): void
    {
        $this->service = new ProductionService();
    }

    public function testMaxStorableLevel0(): void
    {
        // (int)(2.5 * e^0) * 5000 = (int)2.5 * 5000 = 2 * 5000 = 10000
        $this->assertEquals(10000, $this->service->maxStorable(0));
    }

    public function testMaxStorableLevel5(): void
    {
        $result = $this->service->maxStorable(5);
        $this->assertGreaterThan(10000, $result);
    }

    public function testMaxStorableLevel10(): void
    {
        $result = $this->service->maxStorable(10);
        $this->assertGreaterThan($this->service->maxStorable(5), $result);
    }

    public function testMaxProductionPercentageFullEnergy(): void
    {
        // Enough energy: 100%
        $this->assertEquals(100, $this->service->maxProductionPercentage(1000, -500));
    }

    public function testMaxProductionPercentageNoEnergy(): void
    {
        // Zero energy with positive usage: 0%
        $this->assertEquals(0, $this->service->maxProductionPercentage(0, 100));
    }

    public function testMaxProductionPercentagePartialEnergy(): void
    {
        // maxEnergy=50, energyUsed=-200 → (50+(-200)) < 0 → floor(50/200*100) = 25
        $this->assertEquals(25, $this->service->maxProductionPercentage(50, -200));
    }

    public function testMaxProductionPercentageCappedAt100(): void
    {
        // Even if formula exceeds 100, cap it
        $this->assertEquals(100, $this->service->maxProductionPercentage(500, -2));
    }

    public function testMaxProductionPercentageNoUsage(): void
    {
        $this->assertEquals(100, $this->service->maxProductionPercentage(1000, 0));
    }

    public function testProductionAmountEnergy(): void
    {
        // Energy: ceil(100 * 1.5) = 150
        $this->assertEquals(150.0, $this->service->productionAmount(100, 1.5, 0, true));
    }

    public function testProductionAmountResource(): void
    {
        // Resource: floor(100 * 2 * 1.5) = floor(300) = 300
        $this->assertEquals(300.0, $this->service->productionAmount(100, 1.5, 2.0, false));
    }

    public function testProductionAmountEnergyRoundsUp(): void
    {
        // Energy: ceil(10 * 1.1) = ceil(11) = 11
        $this->assertEquals(11.0, $this->service->productionAmount(10, 1.1, 0, true));
    }

    public function testProductionAmountResourceRoundsDown(): void
    {
        // Resource: floor(10 * 1.1 * 1.0) = floor(11) = 11
        $this->assertEquals(11.0, $this->service->productionAmount(10, 1.1, 1.0, false));
    }

    public function testCurrentProduction(): void
    {
        // 500 * 0.01 * 80 = 400
        $this->assertEquals(400.0, $this->service->currentProduction(500, 80));
    }

    public function testCurrentProductionFull(): void
    {
        // 500 * 0.01 * 100 = 500
        $this->assertEquals(500.0, $this->service->currentProduction(500, 100));
    }

    public function testCurrentProductionZero(): void
    {
        $this->assertEquals(0.0, $this->service->currentProduction(500, 0));
    }
}
