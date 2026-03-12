<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Formulas;

use App\Services\Formulas\Expedition;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ExpeditionTest extends TestCase
{
    public function testGetMaxExpeditionPoints(): void
    {
        $expedition = new Expedition();

        $this->assertEquals(2500, $expedition->getMaxExpeditionPoints(50000));
        $this->assertEquals(6000, $expedition->getMaxExpeditionPoints(500000));
        $this->assertEquals(9000, $expedition->getMaxExpeditionPoints(2500000));
        $this->assertEquals(12000, $expedition->getMaxExpeditionPoints(10000000));
        $this->assertEquals(15000, $expedition->getMaxExpeditionPoints(30000000));
        $this->assertEquals(18000, $expedition->getMaxExpeditionPoints(60000000));
        $this->assertEquals(21000, $expedition->getMaxExpeditionPoints(90000000));
        $this->assertEquals(25000, $expedition->getMaxExpeditionPoints(120000000));
    }

    public function testGetMaxShipsExpeditionPoints(): void
    {
        $expedition = new Expedition();

        $this->assertEquals(250000, $expedition->getMaxShipsExpeditionPoints(50000));
        $this->assertEquals(600000, $expedition->getMaxShipsExpeditionPoints(500000));
        $this->assertEquals(900000, $expedition->getMaxShipsExpeditionPoints(2500000));
        $this->assertEquals(1200000, $expedition->getMaxShipsExpeditionPoints(10000000));
        $this->assertEquals(1500000, $expedition->getMaxShipsExpeditionPoints(30000000));
        $this->assertEquals(1800000, $expedition->getMaxShipsExpeditionPoints(60000000));
        $this->assertEquals(2100000, $expedition->getMaxShipsExpeditionPoints(90000000));
        $this->assertEquals(2500000, $expedition->getMaxShipsExpeditionPoints(120000000));
    }

    public function testCalculateExpeditionPoints(): void
    {
        $exampleIntegrity = 800;

        $expedition = new Expedition();
        $result = $expedition->calculateExpeditionPoints($exampleIntegrity);

        $expectedResult = 4; // Expected result for $structuralIntegrity = 800

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetExpeditionResult(): void
    {
        $expedition = new Expedition();
        $result = $expedition->getExpeditionResult();

        $validResults = [
            'darkMatter', 'ships', 'resources', 'pirates', 'aliens', 'delay', 'early', 'nothing', 'merchant', 'blackHole'
        ];

        $this->assertContains($result, $validResults);
    }

    public function testCalculateDarkMatterSourceSize(): void
    {
        $expedition = new Expedition();
        $result = $expedition->calculateDarkMatterSourceSize();

        $validResults = ['small', 'medium', 'large'];

        $this->assertContains($result, $validResults);
    }

    public function testGetDarkMatterSourceSize(): void
    {
        $expedition = new Expedition();

        $resultSmall = $expedition->getDarkMatterSourceSize('small');
        $this->assertGreaterThanOrEqual(300, $resultSmall);
        $this->assertLessThanOrEqual(400, $resultSmall);

        $resultMedium = $expedition->getDarkMatterSourceSize('medium');
        $this->assertGreaterThanOrEqual(500, $resultMedium);
        $this->assertLessThanOrEqual(700, $resultMedium);

        $resultLarge = $expedition->getDarkMatterSourceSize('large');
        $this->assertGreaterThanOrEqual(1000, $resultLarge);
        $this->assertLessThanOrEqual(1800, $resultLarge);
    }

    public function testCalculateResourceTypeObtained(): void
    {
        $expedition = new Expedition();
        $result = $expedition->calculateResourceTypeObtained();

        $validResults = ['metal', 'crystal', 'deuterium'];

        $this->assertContains($result, $validResults);
    }

    public function testCalculateResourceSourceSize(): void
    {
        $expedition = new Expedition();
        $result = $expedition->calculateResourceSourceSize();

        $validResults = ['normal', 'large', 'xl'];

        $this->assertContains($result, $validResults);
    }

    public function testGetResourceSourceSizeMultChances(): void
    {
        $expedition = new Expedition();

        $resultNormal = $expedition->getResourceSourceSizeMultChances('normal');
        $this->assertGreaterThanOrEqual(10, $resultNormal);
        $this->assertLessThanOrEqual(50, $resultNormal);

        $resultLarge = $expedition->getResourceSourceSizeMultChances('large');
        $this->assertGreaterThanOrEqual(50, $resultLarge);
        $this->assertLessThanOrEqual(100, $resultLarge);

        $resultXl = $expedition->getResourceSourceSizeMultChances('xl');
        $this->assertGreaterThanOrEqual(100, $resultXl);
        $this->assertLessThanOrEqual(200, $resultXl);
    }

    public function testGetResourceFoundAmount(): void
    {
        $expedition = new Expedition();

        // Test case 1: metal resource type
        $result = $expedition->getResourceFoundAmount(2, 100, 'metal');
        $this->assertEquals(200, $result);

        // Test case 2: crystal resource type
        $result = $expedition->getResourceFoundAmount(3, 150, 'crystal');
        $this->assertEquals(225, $result);

        // Test case 3: deuterium resource type
        $result = $expedition->getResourceFoundAmount(4, 200, 'deuterium');
        $this->assertEquals(266, $result);
    }

    public function testCalculateShipFoundAmount(): void
    {
        $expedition = new Expedition();

        $result = $expedition->calculateShipFoundAmount(3, 150);
        $this->assertEquals(225, $result);
    }

    public function testGetPossibleShips(): void
    {
        $expedition = new Expedition();

        $result = $expedition->getPossibleShips();

        $expected = [
            202,
            203,
            204,
            205,
            206,
            207,
            210,
            211,
            213,
            215
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetShipsObtainableChances(): void
    {
        $expedition = new Expedition();

        $result = $expedition->getShipsObtainableChances();

        $expected = [
            202 => 0.1,
            203 => 0.1,
            204 => 0.1,
            205 => 0.5,
            206 => 0.25,
            207 => 0.125,
            210 => 0.1,
            211 => 0.0625,
            213 => 0.0625,
            215 => 0.0625
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetFleetDeplay(): void
    {
        $expedition = new Expedition();

        $result = $expedition->getFleetDeplay();
        $this->assertContains($result, [2, 3, 5]); // Assert that the result is one of the allowed values
    }
}
