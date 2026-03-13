<?php

declare(strict_types=1);

namespace Tests\Unit\App\Core\GameObjects;

use App\Core\GameObjects\Price;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $price = new Price();

        $this->assertSame(0, $price->getMetal());
        $this->assertSame(0, $price->getCrystal());
        $this->assertSame(0, $price->getDeuterium());
        $this->assertSame(0, $price->getEnergy());
        $this->assertSame(0, $price->getEnergyMax());
        $this->assertSame(1.0, $price->getFactor());
    }

    public function testGettersReturnConstructorValues(): void
    {
        $price = new Price(
            metal: 60,
            crystal: 15,
            deuterium: 0,
            energy: 10,
            energyMax: 0,
            factor: 1.5,
        );

        $this->assertSame(60, $price->getMetal());
        $this->assertSame(15, $price->getCrystal());
        $this->assertSame(0, $price->getDeuterium());
        $this->assertSame(10, $price->getEnergy());
        $this->assertSame(0, $price->getEnergyMax());
        $this->assertSame(1.5, $price->getFactor());
    }

    public function testToArrayWithEnergyField(): void
    {
        $price = new Price(metal: 60, crystal: 15, energy: 10, factor: 1.5);

        $expected = [
            'metal' => 60,
            'crystal' => 15,
            'deuterium' => 0,
            'factor' => 1.5,
            'energy' => 10,
        ];

        $this->assertSame($expected, $price->toArray());
    }

    public function testToArrayWithEnergyMaxField(): void
    {
        $price = new Price(energyMax: 300000, factor: 3.0);

        $expected = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
            'factor' => 3.0,
            'energy_max' => 300000,
        ];

        $this->assertSame($expected, $price->toArray());
    }

    public function testToArrayEnergyMaxTakesPrecedenceOverEnergy(): void
    {
        $price = new Price(energy: 50, energyMax: 100, factor: 1.0);

        $arr = $price->toArray();

        $this->assertArrayHasKey('energy_max', $arr);
        $this->assertArrayNotHasKey('energy', $arr);
    }
}
