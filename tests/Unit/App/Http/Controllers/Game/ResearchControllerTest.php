<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers\Game;

use App\Http\Controllers\Game\ResearchController;
use App\Models\Planets;
use App\Models\ResearchQueue;
use App\Services\FormatService;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;
use Tests\TestCase;

#[CoversClass(ResearchController::class)]
class ResearchControllerTest extends TestCase
{
    public function testCommanderUsesTopQueueInsteadOfRowCountdownForActiveResearch(): void
    {
        $controller = $this->buildController();
        $planet = new Planets(['planet_id' => 7]);
        $activeItem = new ResearchQueue([
            'tech_id' => 106,
            'planet_id' => 7,
            'end_time' => time() + 300,
        ]);

        $result = $this->callGetActionLink(
            $controller,
            techId: 106,
            isWorking: true,
            queueCount: 1,
            labInQueue: false,
            isOnVacation: false,
            commanderActive: true,
            activeItem: $activeItem,
            planet: $planet,
            workingPlanet: null,
            currentLevel: 0,
        );

        $this->assertStringContainsString('game.php?page=research&cmd=search&tech=106', $result);
        $this->assertStringNotContainsString('id="brp"', $result);
    }

    public function testActiveResearchShowsRowCountdownWithoutCommander(): void
    {
        $controller = $this->buildController();
        $planet = new Planets([
            'planet_id' => 7,
            'planet_galaxy' => 1,
            'planet_system' => 2,
            'planet_planet' => 3,
        ]);
        $activeItem = new ResearchQueue([
            'tech_id' => 106,
            'planet_id' => 7,
            'end_time' => time() + 300,
        ]);

        $result = $this->callGetActionLink(
            $controller,
            techId: 106,
            isWorking: true,
            queueCount: 1,
            labInQueue: false,
            isOnVacation: false,
            commanderActive: false,
            activeItem: $activeItem,
            planet: $planet,
            workingPlanet: null,
            currentLevel: 0,
        );

        $this->assertStringContainsString('id="brp"', $result);
        $this->assertStringContainsString('game.php?page=research&cmd=cancel&tech=106', $result);
    }

    private function buildController(): ResearchController
    {
        return new ResearchController(
            registry: $this->createStub(\App\Core\GameObjects\GameObjectRegistry::class),
            developmentsService: $this->createStub(\App\Services\Game\Formulas\DevelopmentsService::class),
            formatService: new FormatService(),
            developmentDataService: $this->createStub(\App\Services\Game\DevelopmentDataService::class),
            officerService: $this->createStub(\App\Services\Game\Formulas\OfficerService::class),
            settingsService: $this->createStub(\App\Services\SettingsService::class),
            researchQueueService: $this->createStub(\App\Services\Game\ResearchQueueService::class),
            timingService: $this->createStub(\App\Services\TimingService::class),
        );
    }

    private function callGetActionLink(
        ResearchController $controller,
        int $techId,
        bool $isWorking,
        int $queueCount,
        bool $labInQueue,
        bool $isOnVacation,
        bool $commanderActive,
        ?ResearchQueue $activeItem,
        Planets $planet,
        ?Planets $workingPlanet,
        int $currentLevel,
    ): string {
        $method = new ReflectionMethod(ResearchController::class, 'getActionLink');

        $result = $method->invoke(
            $controller,
            $techId,
            $isWorking,
            $queueCount,
            $labInQueue,
            $isOnVacation,
            $commanderActive,
            $activeItem,
            $planet,
            $workingPlanet,
            $currentLevel,
        );

        $this->assertIsString($result);

        return $result;
    }
}
