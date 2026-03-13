<?php

declare(strict_types=1);

namespace Tests\Unit\App\Core\GameObjects;

use App\Core\GameObjects\ProductionFormula;
use PHPUnit\Framework\TestCase;

class ProductionFormulaTest extends TestCase
{
    private function createMetalMineFormula(): ProductionFormula
    {
        return new ProductionFormula(
            baseMetal: 60,
            baseCrystal: 15,
            baseDeuterium: 0,
            factor: 1.5,
            metalFormula: fn (int $level, float $levelFactor): float
                => 30 * $level * pow(1.1, $level) * $levelFactor,
            crystalFormula: fn (int $level, float $levelFactor): float
                => 0,
            deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                => 0,
            energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                => -(10 * $level * pow(1.1, $level)) * $levelFactor,
        );
    }

    public function testGettersReturnConstructorValues(): void
    {
        $formula = $this->createMetalMineFormula();

        $this->assertSame(60, $formula->getBaseMetal());
        $this->assertSame(15, $formula->getBaseCrystal());
        $this->assertSame(0, $formula->getBaseDeuterium());
        $this->assertSame(1.5, $formula->getFactor());
    }

    public function testCalculateMetalAtLevel1(): void
    {
        $formula = $this->createMetalMineFormula();

        $result = $formula->calculateMetal(1, 1.0);

        // 30 * 1 * 1.1^1 * 1.0 = 33.0
        $this->assertEqualsWithDelta(33.0, $result, 0.001);
    }

    public function testCalculateMetalAtLevel5(): void
    {
        $formula = $this->createMetalMineFormula();

        $result = $formula->calculateMetal(5, 1.0);

        // 30 * 5 * 1.1^5 * 1.0
        $expected = 30 * 5 * pow(1.1, 5) * 1.0;
        $this->assertEqualsWithDelta($expected, $result, 0.001);
    }

    public function testCalculateMetalWithLevelFactor(): void
    {
        $formula = $this->createMetalMineFormula();

        $result = $formula->calculateMetal(5, 0.5);

        $expected = 30 * 5 * pow(1.1, 5) * 0.5;
        $this->assertEqualsWithDelta($expected, $result, 0.001);
    }

    public function testCalculateCrystalReturnsZeroForMetalMine(): void
    {
        $formula = $this->createMetalMineFormula();

        $this->assertSame(0.0, $formula->calculateCrystal(5, 1.0));
    }

    public function testCalculateDeuteriumWithTemperature(): void
    {
        $formula = new ProductionFormula(
            baseMetal: 225,
            baseCrystal: 75,
            baseDeuterium: 0,
            factor: 1.5,
            metalFormula: fn (int $level, float $levelFactor): float
                => 0,
            crystalFormula: fn (int $level, float $levelFactor): float
                => 0,
            deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                => 10 * $level * pow(1.1, $level) * (-0.004 * $planetTemp + 1.36) * $levelFactor,
            energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                => -(20 * $level * pow(1.1, $level)) * $levelFactor,
        );

        $result = $formula->calculateDeuterium(5, 1.0, 40.0);

        // 10 * 5 * 1.1^5 * (-0.004 * 40 + 1.36) * 1.0
        $expected = 10 * 5 * pow(1.1, 5) * (-0.004 * 40 + 1.36) * 1.0;
        $this->assertEqualsWithDelta($expected, $result, 0.001);
    }

    public function testCalculateEnergyWithEnergyTech(): void
    {
        $solarSatFormula = new ProductionFormula(
            baseMetal: 0,
            baseCrystal: 2000,
            baseDeuterium: 500,
            factor: 0.5,
            metalFormula: fn (int $level, float $levelFactor): float
                => 0,
            crystalFormula: fn (int $level, float $levelFactor): float
                => 0,
            deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                => 0,
            energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                => (($planetTemp + 140) / 6) * (0.1 * $levelFactor) * $level,
        );

        $result = $solarSatFormula->calculateEnergy(10, 1.0, 80.0, 5);

        // ((80 + 140) / 6) * (0.1 * 1.0) * 10
        $expected = ((80.0 + 140.0) / 6.0) * (0.1 * 1.0) * 10;
        $this->assertEqualsWithDelta($expected, $result, 0.001);
    }

    public function testCalculateEnergyNegativeForMine(): void
    {
        $formula = $this->createMetalMineFormula();

        $result = $formula->calculateEnergy(5, 1.0);

        // -(10 * 5 * 1.1^5) * 1.0
        $expected = -(10 * 5 * pow(1.1, 5)) * 1.0;
        $this->assertEqualsWithDelta($expected, $result, 0.001);
        $this->assertLessThan(0, $result);
    }

    public function testToLegacyArray(): void
    {
        $formula = $this->createMetalMineFormula();

        $legacy = $formula->toLegacyArray();

        $this->assertSame(60, $legacy['metal']);
        $this->assertSame(15, $legacy['crystal']);
        $this->assertSame(0, $legacy['deuterium']);
        $this->assertSame(0, $legacy['energy']);
        $this->assertSame(1.5, $legacy['factor']);
    }

    public function testCalculateMetalAtLevelZero(): void
    {
        $formula = $this->createMetalMineFormula();

        $result = $formula->calculateMetal(0, 1.0);

        // 30 * 0 * ... = 0
        $this->assertSame(0.0, $result);
    }
}
