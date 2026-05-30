<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game\Formulas;

use App\Core\GameObjects\GameObjectRegistry;
use App\Services\Game\Formulas\FleetsService;
use PHPUnit\Framework\TestCase;

class FleetsServiceTest extends TestCase
{
    private FleetsService $service;

    protected function setUp(): void
    {
        $this->service = new FleetsService(new GameObjectRegistry());
    }

    public function testTargetDistanceSamePosition(): void
    {
        $this->assertEquals(5, $this->service->targetDistance(1, 1, 1, 1, 1, 1));
    }

    public function testTargetDistanceDifferentPlanet(): void
    {
        // abs(3 - 1) * 5 + 1000 = 1010
        $this->assertEquals(1010, $this->service->targetDistance(1, 1, 1, 1, 1, 3));
    }

    public function testTargetDistanceDifferentSystem(): void
    {
        // abs(10 - 5) * 5 * 19 + 2700 = 3175
        $this->assertEquals(3175, $this->service->targetDistance(1, 1, 5, 10, 1, 1));
    }

    public function testTargetDistanceDifferentGalaxy(): void
    {
        // abs(3 - 1) * 20000 = 40000
        $this->assertEquals(40000, $this->service->targetDistance(1, 3, 1, 1, 1, 1));
    }

    public function testTargetDistanceGalaxyTakesPrecedence(): void
    {
        // Different galaxy, system, and planet — galaxy distance wins
        $this->assertEquals(20000, $this->service->targetDistance(1, 2, 5, 10, 3, 7));
    }

    public function testMissionDuration(): void
    {
        $result = $this->service->missionDuration(100, 10000, 5000, 1);
        $this->assertGreaterThan(0, $result);
    }

    public function testMissionDurationAcceptsCalculatedFleetSpeed(): void
    {
        $fleetSpeeds = $this->service->fleetMaxSpeed([202 => 1], 0, 5, 0);
        $this->assertNotEmpty($fleetSpeeds);

        /** @var non-empty-array<int, float> $fleetSpeeds */
        $result = $this->service->missionDuration(100, min($fleetSpeeds), 5000, 1);

        $this->assertGreaterThan(0, $result);
    }

    public function testGetShipSpeedSmallCargoNoCombustion(): void
    {
        // Small cargo (202): base speed 5000, combustion drive, 0 tech
        $speed = $this->service->getShipSpeed(202, 0, 0, 0);
        $this->assertEquals(5000.0, $speed);
    }

    public function testGetShipSpeedSmallCargoCombustionOnly(): void
    {
        // Small cargo (202): combustion primary, impulse at 5 upgrades to impulse
        // With combustion=3, impulse=0: uses combustion. 5000 + 5000 * 3 * 0.1 = 6500
        $speed = $this->service->getShipSpeed(202, 3, 0, 0);
        $this->assertEquals(6500.0, $speed);
    }

    public function testGetShipSpeedSmallCargoImpulseUpgrade(): void
    {
        // Small cargo (202): impulse >= 5 switches to impulse drive, speed2 = 10000
        // 10000 + 10000 * 5 * 0.2 = 20000
        $speed = $this->service->getShipSpeed(202, 3, 5, 0);
        $this->assertEquals(20000.0, $speed);
    }

    public function testGetShipSpeedRecyclerCombustion(): void
    {
        // Recycler (209): primary combustion, secondary impulse at 17, tertiary hyperspace at 15
        // combustion=5, impulse=0, hyperspace=0 → combustion. 2000 + 2000 * 5 * 0.1 = 3000
        $speed = $this->service->getShipSpeed(209, 5, 0, 0);
        $this->assertEquals(3000.0, $speed);
    }

    public function testGetShipSpeedRecyclerImpulseUpgrade(): void
    {
        // Recycler (209): impulse >= 17 switches to impulse, speed2 = 2000
        // 2000 + 2000 * 17 * 0.2 = 8800
        $speed = $this->service->getShipSpeed(209, 5, 17, 0);
        $this->assertEquals(8800.0, $speed);
    }

    public function testGetShipSpeedRecyclerHyperspaceUpgrade(): void
    {
        // Recycler (209): hyperspace >= 15 switches to hyperspace, speed2 = 2000
        // 2000 + 2000 * 15 * 0.3 = 11000
        $speed = $this->service->getShipSpeed(209, 5, 17, 15);
        $this->assertEquals(11000.0, $speed);
    }

    public function testGetShipSpeedBomberImpulse(): void
    {
        // Bomber (211): primary impulse, secondary hyperspace at 8. speed=4000, speed2=5000
        // impulse=6, hyperspace=0 → impulse drive. 4000 + 4000 * 6 * 0.2 = 8800
        $speed = $this->service->getShipSpeed(211, 0, 6, 0);
        $this->assertEquals(8800.0, $speed);
    }

    public function testGetShipSpeedBomberHyperspaceUpgrade(): void
    {
        // Bomber (211): secondary=Hyperspace at minLevel=8
        // hyperspace=8 triggers upgrade, speed2=5000
        // 5000 + 5000 * 8 * 0.3 = 17000
        $speed = $this->service->getShipSpeed(211, 0, 6, 8);
        $this->assertEquals(17000.0, $speed);
    }

    public function testGetShipSpeedZeroReturnsZero(): void
    {
        $this->assertEquals(0.0, $this->service->getShipSpeed(0, 0, 0, 0));
    }

    public function testFleetMaxSpeedReturnsAllShips(): void
    {
        $fleet = [202 => 10, 203 => 5];
        $speeds = $this->service->fleetMaxSpeed($fleet, 3, 0, 0);

        $this->assertArrayHasKey(202, $speeds);
        $this->assertArrayHasKey(203, $speeds);
        $this->assertCount(2, $speeds);
    }

    public function testShipConsumptionSmallCargoDefault(): void
    {
        // Small cargo (202): impulse < 5 → consumption = 10
        $this->assertEquals(10, $this->service->shipConsumption(202, 0, 0, 0));
    }

    public function testShipConsumptionSmallCargoUpgraded(): void
    {
        // Small cargo (202): impulse >= 5 → consumption2 = 20
        $this->assertEquals(20, $this->service->shipConsumption(202, 0, 5, 0));
    }

    public function testFleetConsumption(): void
    {
        $fleet = [204 => 100]; // 100 light fighters
        $result = $this->service->fleetConsumption($fleet, 1, 1000, 5000, 3, 0, 0);

        $this->assertGreaterThan(0, $result);
    }

    public function testGetMaxExpeditions(): void
    {
        $this->assertEquals(0, $this->service->getMaxExpeditions(0));
        $this->assertEquals(1, $this->service->getMaxExpeditions(1));
        $this->assertEquals(2, $this->service->getMaxExpeditions(4));
        $this->assertEquals(3, $this->service->getMaxExpeditions(9));
        $this->assertEquals(3, $this->service->getMaxExpeditions(10));
    }

    public function testGetMaxColonies(): void
    {
        $this->assertEquals(0, $this->service->getMaxColonies(0));
        $this->assertEquals(1, $this->service->getMaxColonies(1));
        $this->assertEquals(1, $this->service->getMaxColonies(2));
        $this->assertEquals(2, $this->service->getMaxColonies(3));
        $this->assertEquals(5, $this->service->getMaxColonies(10));
    }

    public function testGetMaxStorage(): void
    {
        // 5000 + 5000 * 0.05 * 10 = 7500
        $this->assertEquals(7500, $this->service->getMaxStorage(5000, 10));
    }

    public function testGetMaxStorageNoTech(): void
    {
        $this->assertEquals(5000, $this->service->getMaxStorage(5000, 0));
    }

    public function testIsFleetReturning(): void
    {
        $this->assertTrue($this->service->isFleetReturning(1));
        $this->assertTrue($this->service->isFleetReturning('1'));
        $this->assertFalse($this->service->isFleetReturning(0));
        $this->assertFalse($this->service->isFleetReturning(2));
    }
}
