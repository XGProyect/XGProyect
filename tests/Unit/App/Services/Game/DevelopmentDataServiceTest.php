<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Enums\Game\BuildingId;
use App\Enums\Game\ResearchId;
use App\Models\Buildings;
use App\Models\Planets;
use App\Services\FormatService;
use App\Services\Game\DevelopmentDataService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\FormulasService;
use App\Services\SettingsService;
use PHPUnit\Framework\TestCase;

class DevelopmentDataServiceTest extends TestCase
{
    private DevelopmentDataService $service;

    protected function setUp(): void
    {
        if (!defined('TECHNOCRATE_SPEED')) {
            define('TECHNOCRATE_SPEED', 0.25);
        }

        $settingsService = $this->createMock(SettingsService::class);
        $settingsService->method('getInt')->willReturn(2500);

        $registry = new GameObjectRegistry();
        $formulas = new FormulasService($settingsService);
        $developmentsService = new DevelopmentsService($registry, $formulas);

        $this->service = new DevelopmentDataService($registry, $developmentsService, new FormatService());
    }

    public function test_levels_from_data_combines_planet_and_user_levels(): void
    {
        $planetData = [
            'building_laboratory' => 6,
        ];
        $userData = [
            'research_energy_technology' => 4,
        ];

        $levels = $this->service->levelsFromData($planetData, $userData);

        $this->assertSame(6, $levels[BuildingId::Laboratory->value]);
        $this->assertSame(4, $levels[ResearchId::EnergyTechnology->value]);
    }

    public function test_levels_from_planet_uses_loaded_buildings_relation(): void
    {
        $planet = new Planets();
        $planet->setRelation('buildings', new Buildings([
            'building_laboratory' => 8,
        ]));

        $levels = $this->service->levelsFromPlanet($planet, [
            'research_energy_technology' => 5,
        ]);

        $this->assertSame(8, $levels[BuildingId::Laboratory->value]);
        $this->assertSame(5, $levels[ResearchId::EnergyTechnology->value]);
    }

    public function test_planet_resources_supports_array_input(): void
    {
        $resources = $this->service->planetResources([
            'planet_metal' => 1200,
            'planet_crystal' => 900,
            'planet_deuterium' => 600,
            'planet_energy_max' => 50,
        ]);

        $this->assertSame(
            [
                'planet_metal' => 1200.0,
                'planet_crystal' => 900.0,
                'planet_deuterium' => 600.0,
                'planet_energy_max' => 50.0,
            ],
            $resources
        );
    }

    public function test_planet_resources_supports_planet_model_input(): void
    {
        $planet = new Planets([
            'planet_metal' => 700,
            'planet_crystal' => 500,
            'planet_deuterium' => 300,
            'planet_energy_max' => 25,
        ]);

        $resources = $this->service->planetResources($planet);

        $this->assertSame(
            [
                'planet_metal' => 700.0,
                'planet_crystal' => 500.0,
                'planet_deuterium' => 300.0,
                'planet_energy_max' => 25.0,
            ],
            $resources
        );
    }
}
