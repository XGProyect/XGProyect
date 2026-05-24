<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Services\Game\Formulas\ExpeditionService;
use App\Services\SettingsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ExpeditionServiceTest extends TestCase
{
    /** @var array<string, int> */
    private const DEFAULT_WEIGHTS = [
        'expedition_result_dark_matter_weight' => 900,
        'expedition_result_ships_weight' => 2200,
        'expedition_result_resources_weight' => 3250,
        'expedition_result_pirates_weight' => 560,
        'expedition_result_aliens_weight' => 260,
        'expedition_result_delay_weight' => 700,
        'expedition_result_early_weight' => 200,
        'expedition_result_nothing_weight' => 1880,
        'expedition_result_merchant_weight' => 17,
        'expedition_result_black_hole_weight' => 33,
        'expedition_dark_matter_source_small_weight' => 8900,
        'expedition_dark_matter_source_medium_weight' => 1000,
        'expedition_dark_matter_source_large_weight' => 100,
        'expedition_resource_type_metal_weight' => 6850,
        'expedition_resource_type_crystal_weight' => 2400,
        'expedition_resource_type_deuterium_weight' => 750,
        'expedition_resource_source_normal_weight' => 8900,
        'expedition_resource_source_large_weight' => 1000,
        'expedition_resource_source_xl_weight' => 100,
        'expedition_fleet_delay_2_weight' => 8900,
        'expedition_fleet_delay_3_weight' => 1000,
        'expedition_fleet_delay_5_weight' => 100,
    ];

    public function testGetMaxExpeditionPoints(): void
    {
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();

        $this->assertEquals(250000, $expedition->getMaxShipsExpeditionPoints(50000));
        $this->assertEquals(600000, $expedition->getMaxShipsExpeditionPoints(500000));
        $this->assertEquals(900000, $expedition->getMaxShipsExpeditionPoints(2500000));
        $this->assertEquals(1200000, $expedition->getMaxShipsExpeditionPoints(10000000));
        $this->assertEquals(1500000, $expedition->getMaxShipsExpeditionPoints(30000000));
        $this->assertEquals(1800000, $expedition->getMaxShipsExpeditionPoints(60000000));
        $this->assertEquals(2100000, $expedition->getMaxShipsExpeditionPoints(90000000));
        $this->assertEquals(2500000, $expedition->getMaxShipsExpeditionPoints(120000000));
    }

    public function testMaxExpeditionPointsAcceptStatisticFloats(): void
    {
        $expedition = $this->expeditionService();

        $this->assertEquals(6000, $expedition->getMaxExpeditionPoints(500000.0));
        $this->assertEquals(600000, $expedition->getMaxShipsExpeditionPoints(500000.0));
    }

    public function testCalculateExpeditionPoints(): void
    {
        $exampleIntegrity = 800;

        $expedition = $this->expeditionService();
        $result = $expedition->calculateExpeditionPoints($exampleIntegrity);

        $expectedResult = 4; // Expected result for $structuralIntegrity = 800

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetExpeditionResult(): void
    {
        $expedition = $this->expeditionService();
        $result = $expedition->getExpeditionResult();

        $validResults = [
            'darkMatter', 'ships', 'resources', 'pirates', 'aliens', 'delay', 'early', 'nothing', 'merchant', 'blackHole'
        ];

        $this->assertContains($result, $validResults);
    }

    public function testExpeditionWeightTablesUseExpectedOdds(): void
    {
        $expedition = $this->expeditionService();

        $this->assertSame([
            'darkMatter' => 900,
            'ships' => 2200,
            'resources' => 3250,
            'pirates' => 560,
            'aliens' => 260,
            'delay' => 700,
            'early' => 200,
            'nothing' => 1880,
            'merchant' => 17,
            'blackHole' => 33,
        ], $expedition->getExpeditionResultWeights());

        $this->assertSame([
            'small' => 8900,
            'medium' => 1000,
            'large' => 100,
        ], $expedition->getDarkMatterSourceSizeWeights());

        $this->assertSame([
            'metal' => 6850,
            'crystal' => 2400,
            'deuterium' => 750,
        ], $expedition->getResourceTypeWeights());

        $this->assertSame([
            'normal' => 8900,
            'large' => 1000,
            'xl' => 100,
        ], $expedition->getResourceSourceSizeWeights());

        $this->assertSame([
            2 => 8900,
            3 => 1000,
            5 => 100,
        ], $expedition->getFleetDelayWeights());
    }

    public function testCalculateDarkMatterSourceSize(): void
    {
        $expedition = $this->expeditionService();
        $result = $expedition->calculateDarkMatterSourceSize();

        $validResults = ['small', 'medium', 'large'];

        $this->assertContains($result, $validResults);
    }

    public function testGetDarkMatterSourceSize(): void
    {
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();
        $result = $expedition->calculateResourceTypeObtained();

        $validResults = ['metal', 'crystal', 'deuterium'];

        $this->assertContains($result, $validResults);
    }

    public function testCalculateResourceSourceSize(): void
    {
        $expedition = $this->expeditionService();
        $result = $expedition->calculateResourceSourceSize();

        $validResults = ['normal', 'large', 'xl'];

        $this->assertContains($result, $validResults);
    }

    public function testGetResourceSourceSizeMultChances(): void
    {
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();

        $result = $expedition->calculateShipFoundAmount(3, 150);
        $this->assertEquals(225, $result);
    }

    public function testGetPossibleShips(): void
    {
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();

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
        $expedition = $this->expeditionService();

        $result = $expedition->getFleetDeplay();
        $this->assertContains($result, [2, 3, 5]); // Assert that the result is one of the allowed values
    }

    /** @param array<string, int> $overrides */
    private function expeditionService(array $overrides = []): ExpeditionService
    {
        $settings = $this->createStub(SettingsService::class);
        $weights = array_merge(self::DEFAULT_WEIGHTS, $overrides);

        $settings->method('getInt')->willReturnCallback(
            static function (string $setting) use ($weights): int {
                if (!array_key_exists($setting, $weights)) {
                    throw new InvalidArgumentException('Missing setting: ' . $setting);
                }

                return $weights[$setting];
            }
        );

        return new ExpeditionService($settings);
    }
}
