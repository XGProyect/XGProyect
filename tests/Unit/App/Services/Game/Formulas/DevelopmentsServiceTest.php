<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Core\GameObjects\GameObjectRegistry;
use App\Enums\Game\BuildingId;
use App\Enums\Game\ResearchId;
use App\Enums\Game\ShipId;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\FormulasService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class DevelopmentsServiceTest extends TestCase
{
    private DevelopmentsService $service;

    protected function setUp(): void
    {
        if (!defined('FIELDS_BY_TERRAFORMER')) {
            define('FIELDS_BY_TERRAFORMER', 5);
        }

        if (!defined('TECHNOCRATE_SPEED')) {
            define('TECHNOCRATE_SPEED', 0.25);
        }

        $settingsService = $this->createMock(SettingsService::class);
        $settingsService->method('getInt')->willReturn(2500);

        $registry = new GameObjectRegistry();
        $formulas = new FormulasService($settingsService);

        $this->service = new DevelopmentsService($registry, $formulas);
    }

    public function testSetBuildingPageSupplies(): void
    {
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::MetalMine->value));
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::CrystalMine->value));
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::DeuteriumSynthesizer->value));
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::SolarPlant->value));
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::FusionReactor->value));
        $this->assertEquals('supplies', $this->service->setBuildingPage(BuildingId::MetalStore->value));
    }

    public function testSetBuildingPageFacilities(): void
    {
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::RobotFactory->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::Hangar->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::Laboratory->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::Terraformer->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::MissileSilo->value));
    }

    public function testSetBuildingPageMoon(): void
    {
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::Mondbasis->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::Phalanx->value));
        $this->assertEquals('facilities', $this->service->setBuildingPage(BuildingId::JumpGate->value));
    }

    public function testSetBuildingPageNonBuilding(): void
    {
        // Ship ID should return 'overview'
        $this->assertEquals('overview', $this->service->setBuildingPage(ShipId::SmallCargo->value));
    }

    public function testSetBuildingPageInvalidId(): void
    {
        $this->assertEquals('overview', $this->service->setBuildingPage(9999));
    }

    public function testMaxFields(): void
    {
        // 163 + (5 * 5) = 188
        $this->assertEquals(188, $this->service->maxFields(163, 5));
    }

    public function testMaxFieldsNoTerraformer(): void
    {
        $this->assertEquals(163, $this->service->maxFields(163, 0));
    }

    public function testDevelopmentPriceMetalMineLevel0(): void
    {
        // Metal mine: metal=60, crystal=15, factor=1.5, level=0
        // round(60 * 1.5^0) = 60, round(15 * 1.5^0) = 15
        $cost = $this->service->developmentPrice(BuildingId::MetalMine->value, 0);

        $this->assertEquals(60, $cost['metal']);
        $this->assertEquals(15, $cost['crystal']);
    }

    public function testDevelopmentPriceMetalMineLevel5(): void
    {
        // Metal mine: metal=60, crystal=15, factor=1.5, level=5
        // round(60 * 1.5^5) = 455, round(15 * 1.5^5) = 114
        $cost = $this->service->developmentPrice(BuildingId::MetalMine->value, 5);

        $this->assertEquals(456, $cost['metal']);
        $this->assertEquals(114, $cost['crystal']);
    }

    public function testDevelopmentPriceNonIncremental(): void
    {
        // Non-incremental: just floor of base price
        $cost = $this->service->developmentPrice(BuildingId::MetalMine->value, 10, false);

        $this->assertEquals(60, $cost['metal']);
        $this->assertEquals(15, $cost['crystal']);
    }

    public function testIsDevelopmentPayableTrue(): void
    {
        $resources = [
            'planet_metal' => 1000,
            'planet_crystal' => 1000,
            'planet_deuterium' => 1000,
            'planet_energy_max' => 0,
        ];

        $this->assertTrue($this->service->isDevelopmentPayable($resources, BuildingId::MetalMine->value, 0));
    }

    public function testIsDevelopmentPayableFalse(): void
    {
        $resources = [
            'planet_metal' => 0,
            'planet_crystal' => 0,
            'planet_deuterium' => 0,
            'planet_energy_max' => 0,
        ];

        $this->assertFalse($this->service->isDevelopmentPayable($resources, BuildingId::MetalMine->value, 5));
    }

    public function testDevelopmentTimeBuildingReturnsPositive(): void
    {
        $time = $this->service->developmentTime(
            BuildingId::MetalMine->value,
            5,
            5,  // robotics factory
            0,  // nanite factory
            0,  // total lab level
            0,  // astrophysics level
            false
        );

        $this->assertGreaterThan(0, $time);
    }

    public function testDevelopmentTimeResearch(): void
    {
        $time = $this->service->developmentTime(
            ResearchId::EnergyTechnology->value,
            1,
            0,  // robotics factory (not relevant)
            0,  // nanite factory (not relevant)
            5,  // total lab level
            0,  // astrophysics level
            false
        );

        $this->assertGreaterThan(0, $time);
    }

    public function testDevelopmentTimeResearchWithTechnocrate(): void
    {
        $timeWithout = $this->service->developmentTime(
            ResearchId::EnergyTechnology->value,
            1,
            0,
            0,
            5,
            0,
            false
        );

        $timeWith = $this->service->developmentTime(
            ResearchId::EnergyTechnology->value,
            1,
            0,
            0,
            5,
            0,
            true
        );

        $this->assertLessThan($timeWithout, $timeWith);
    }

    public function testTearDownTime(): void
    {
        $time = $this->service->tearDownTime(
            BuildingId::MetalMine->value,
            5,
            3,  // robotics factory
            0   // nanite factory
        );

        $this->assertGreaterThan(0, $time);
    }

    public function testIsDevelopmentAllowedNoRequirements(): void
    {
        // Metal mine has no requirements
        $this->assertTrue($this->service->isDevelopmentAllowed(BuildingId::MetalMine->value, []));
    }

    public function testIsDevelopmentAllowedMet(): void
    {
        // Fusion reactor requires deuterium synth 5 and energy tech 3
        $levels = [3 => 5, 113 => 3];
        $this->assertTrue($this->service->isDevelopmentAllowed(BuildingId::FusionReactor->value, $levels));
    }

    public function testIsDevelopmentAllowedNotMet(): void
    {
        // Fusion reactor requires deuterium synth 5 and energy tech 3
        $levels = [3 => 2, 113 => 1];
        $this->assertFalse($this->service->isDevelopmentAllowed(BuildingId::FusionReactor->value, $levels));
    }

    public function testIsDevelopmentAllowedPartiallyMet(): void
    {
        // Only one requirement met
        $levels = [3 => 5, 113 => 1];
        $this->assertFalse($this->service->isDevelopmentAllowed(BuildingId::FusionReactor->value, $levels));
    }

    public function testIsLabWorking(): void
    {
        $this->assertTrue($this->service->isLabWorking(106));
        $this->assertFalse($this->service->isLabWorking(0));
    }

    public function testIsShipyardWorking(): void
    {
        $this->assertTrue($this->service->isShipyardWorking(1));
        $this->assertFalse($this->service->isShipyardWorking(0));
    }

    public function testAreFieldsAvailable(): void
    {
        // 100 current < 163 + 0 = 163 max
        $this->assertTrue($this->service->areFieldsAvailable(100, 163, 0));
    }

    public function testAreFieldsNotAvailable(): void
    {
        // 163 current = 163 + 0 = 163 max
        $this->assertFalse($this->service->areFieldsAvailable(163, 163, 0));
    }

    public function testAreFieldsAvailableWithTerraformer(): void
    {
        // 163 current < 163 + (2 * 5) = 173 max
        $this->assertTrue($this->service->areFieldsAvailable(163, 163, 2));
    }
}
