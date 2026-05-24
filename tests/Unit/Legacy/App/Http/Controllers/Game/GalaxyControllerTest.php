<?php

declare(strict_types=1);

namespace Tests\Unit\Legacy\App\Http\Controllers\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Services\FormatService;
use App\Services\Game\Formulas\FleetsService;
use App\Services\Game\Formulas\OfficerService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Xgp\App\Http\Controllers\Game\GalaxyController;
use Xgp\App\Libraries\GalaxyLib;

#[CoversClass(GalaxyController::class)]
class GalaxyControllerTest extends TestCase
{
    public function testDebrisFleetTargetLooksUpPlanetRow(): void
    {
        $this->assertSame(
            GalaxyLib::PLANET_TYPE,
            $this->targetLookupPlanetType(GalaxyLib::DEBRIS_TYPE)
        );
    }

    public function testPlanetAndMoonFleetTargetsKeepTheirLookupType(): void
    {
        $this->assertSame(
            GalaxyLib::PLANET_TYPE,
            $this->targetLookupPlanetType(GalaxyLib::PLANET_TYPE)
        );
        $this->assertSame(
            GalaxyLib::MOON_TYPE,
            $this->targetLookupPlanetType(GalaxyLib::MOON_TYPE)
        );
    }

    private function targetLookupPlanetType(int $planetType): int
    {
        $controller = new GalaxyController(
            new FormatService(),
            new FleetsService($this->createStub(GameObjectRegistry::class)),
            new OfficerService(),
        );
        $method = new ReflectionMethod(GalaxyController::class, 'targetLookupPlanetType');

        return $method->invoke($controller, $planetType);
    }
}
