<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Http\Controllers\Game\PreferencesController;
use App\Models\Preferences;
use App\Services\FormatService;
use App\Services\SettingsService;
use App\Services\TimingService;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;
use Tests\TestCase;

#[CoversClass(PreferencesController::class)]
class PreferencesControllerTest extends TestCase
{
    public function testVacationProductionUpdateUsesRegisteredProducers(): void
    {
        $result = $this->callProducerPercentUpdate(0);

        $this->assertSame([
            'planet_building_metal_mine_percent' => 0,
            'planet_building_crystal_mine_percent' => 0,
            'planet_building_deuterium_sintetizer_percent' => 0,
            'planet_building_solar_plant_percent' => 0,
            'planet_building_fusion_reactor_percent' => 0,
            'planet_ship_solar_satellite_percent' => 0,
        ], $result);
    }

    public function testSortPlanetOptionsExposeSelectedStateAsBoolean(): void
    {
        $controller = $this->buildController();
        $preferences = new Preferences(['preference_planet_sort' => 3]);
        $method = new ReflectionMethod(PreferencesController::class, 'sortPlanetOptions');

        $result = $method->invoke($controller, $preferences);

        $this->assertIsArray($result);

        /** @var array<int, array{value: int, selected: bool, text: string}> $result */
        $this->assertTrue($result[3]['selected']);
        $this->assertFalse($result[0]['selected']);
    }

    /**
     * @return array<string, int>
     */
    private function callProducerPercentUpdate(int $level): array
    {
        $method = new ReflectionMethod(PreferencesController::class, 'producerPercentUpdate');

        $result = $method->invoke($this->buildController(), $level);

        $this->assertIsArray($result);

        /** @var array<string, int> $result */
        return $result;
    }

    private function buildController(): PreferencesController
    {
        return new PreferencesController(
            formatService: new FormatService(),
            timingService: $this->createStub(TimingService::class),
            settingsService: $this->createStub(SettingsService::class),
            registry: new GameObjectRegistry(),
        );
    }
}
