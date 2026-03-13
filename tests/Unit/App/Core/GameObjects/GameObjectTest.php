<?php

declare(strict_types=1);

namespace Tests\Unit\App\Core\GameObjects;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\DriveSpec;
use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\Officer;
use App\Core\GameObjects\Price;
use App\Core\GameObjects\ProductionFormula;
use App\Core\GameObjects\Research;
use App\Core\GameObjects\Ship;
use App\Enums\Game\BuildingCategory;
use App\Enums\Game\DriveType;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class GameObjectTest extends TestCase
{
    // ── Building ──────────────────────────────────────────────────────

    public function testBuildingImplementsInterface(): void
    {
        $building = $this->createBuilding();

        $this->assertInstanceOf(GameObjectInterface::class, $building);
    }

    public function testBuildingGetters(): void
    {
        $building = $this->createBuilding();

        $this->assertSame(1, $building->getId());
        $this->assertSame('building_metal_mine', $building->getName());
        $this->assertSame(60, $building->getPrice()->getMetal());
        $this->assertSame(BuildingCategory::Resource, $building->getCategory());
        $this->assertTrue($building->getRequirements()->isEmpty());
    }

    public function testBuildingWithProduction(): void
    {
        $production = $this->createDummyProduction();
        $building = new Building(
            id: 1,
            name: 'building_metal_mine',
            price: new Price(metal: 60, crystal: 15, factor: 1.5),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
            production: $production,
        );

        $this->assertTrue($building->hasProduction());
        $this->assertSame($production, $building->getProduction());
    }

    public function testBuildingWithoutProduction(): void
    {
        $building = $this->createBuilding();

        $this->assertFalse($building->hasProduction());
        $this->assertNull($building->getProduction());
    }

    public function testBuildingRequirements(): void
    {
        $building = new Building(
            id: 21,
            name: 'building_shipyard',
            price: new Price(metal: 400, crystal: 200, deuterium: 100, factor: 2),
            requirements: new Collection([14 => 2]),
            category: BuildingCategory::Facility,
        );

        $this->assertFalse($building->getRequirements()->isEmpty());
        $this->assertSame(2, $building->getRequirements()->get(14));
    }

    // ── Research ─────────────────────────────────────────────────────

    public function testResearchGetters(): void
    {
        $research = new Research(
            id: 106,
            name: 'research_espionage_technology',
            price: new Price(metal: 200, crystal: 1000, deuterium: 200, factor: 2),
            requirements: new Collection([31 => 3]),
        );

        $this->assertInstanceOf(GameObjectInterface::class, $research);
        $this->assertSame(106, $research->getId());
        $this->assertSame('research_espionage_technology', $research->getName());
        $this->assertSame(1000, $research->getPrice()->getCrystal());
        $this->assertSame(3, $research->getRequirements()->get(31));
    }

    // ── Ship ─────────────────────────────────────────────────────────

    public function testShipGetters(): void
    {
        $ship = $this->createShip();

        $this->assertInstanceOf(GameObjectInterface::class, $ship);
        $this->assertSame(202, $ship->getId());
        $this->assertSame('ship_small_cargo', $ship->getName());
        $this->assertSame(10.0, $ship->getShield());
        $this->assertSame(5.0, $ship->getAttack());
        $this->assertSame(5000, $ship->getSpeed());
        $this->assertSame(10000, $ship->getSpeed2());
        $this->assertSame(20, $ship->getConsumption());
        $this->assertSame(20, $ship->getConsumption2());
        $this->assertSame(5000, $ship->getCapacity());
    }

    public function testShipRapidFire(): void
    {
        $ship = $this->createShip();

        $this->assertSame(5, $ship->getRapidFire()->get(210));
    }

    public function testShipDrive(): void
    {
        $ship = $this->createShip();

        $this->assertSame(DriveType::Combustion, $ship->getDrive()->getPrimary());
        $this->assertSame(DriveType::Impulse, $ship->getDrive()->getSecondary());
    }

    public function testShipWithProduction(): void
    {
        $production = $this->createDummyProduction();
        $ship = new Ship(
            id: 212,
            name: 'ship_solar_satellite',
            price: new Price(crystal: 2000, deuterium: 500, factor: 0.5),
            requirements: new Collection([21 => 1]),
            shield: 1,
            attack: 1,
            rapidFire: new Collection(),
            speed: 0,
            speed2: 0,
            consumption: 0,
            consumption2: 0,
            capacity: 0,
            drive: new DriveSpec(primary: DriveType::None),
            production: $production,
        );

        $this->assertTrue($ship->hasProduction());
        $this->assertSame($production, $ship->getProduction());
    }

    public function testShipWithoutProduction(): void
    {
        $ship = $this->createShip();

        $this->assertFalse($ship->hasProduction());
        $this->assertNull($ship->getProduction());
    }

    // ── Defense ──────────────────────────────────────────────────────

    public function testDefenseGetters(): void
    {
        $defense = new Defense(
            id: 401,
            name: 'defense_rocket_launcher',
            price: new Price(metal: 2000, factor: 1),
            requirements: new Collection([21 => 1]),
            shield: 20,
            attack: 80,
            rapidFire: new Collection(),
        );

        $this->assertInstanceOf(GameObjectInterface::class, $defense);
        $this->assertSame(401, $defense->getId());
        $this->assertSame(20.0, $defense->getShield());
        $this->assertSame(80.0, $defense->getAttack());
        $this->assertTrue($defense->getRapidFire()->isEmpty());
    }

    public function testDefenseWithRapidFire(): void
    {
        $defense = new Defense(
            id: 407,
            name: 'defense_plasma_turret',
            price: new Price(metal: 50000, crystal: 50000, deuterium: 30000, factor: 1),
            requirements: new Collection([21 => 8]),
            shield: 300,
            attack: 3000,
            rapidFire: new Collection([210 => 5]),
        );

        $this->assertFalse($defense->getRapidFire()->isEmpty());
        $this->assertSame(5, $defense->getRapidFire()->get(210));
    }

    // ── Officer ──────────────────────────────────────────────────────

    public function testOfficerImplementsInterface(): void
    {
        $officer = $this->createOfficer();

        $this->assertInstanceOf(GameObjectInterface::class, $officer);
    }

    public function testOfficerGetters(): void
    {
        $officer = $this->createOfficer();

        $this->assertSame(601, $officer->getId());
        $this->assertSame('officer_commander', $officer->getName());
        $this->assertSame(3000, $officer->getDarkmatterWeek());
        $this->assertSame(10000, $officer->getDarkmatterMonth());
        $this->assertSame('commander_big.png', $officer->getImgBig());
        $this->assertSame('commander_small.png', $officer->getImgSmall());
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function createBuilding(): Building
    {
        return new Building(
            id: 1,
            name: 'building_metal_mine',
            price: new Price(metal: 60, crystal: 15, factor: 1.5),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
        );
    }

    private function createShip(): Ship
    {
        return new Ship(
            id: 202,
            name: 'ship_small_cargo',
            price: new Price(metal: 2000, crystal: 2000, factor: 1),
            requirements: new Collection([21 => 2, 115 => 2]),
            shield: 10,
            attack: 5,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 5000,
            speed2: 10000,
            consumption: 20,
            consumption2: 20,
            capacity: 5000,
            drive: new DriveSpec(
                primary: DriveType::Combustion,
                secondary: DriveType::Impulse,
                secondaryMinLevel: 5,
            ),
        );
    }

    private function createOfficer(): Officer
    {
        return new Officer(
            id: 601,
            name: 'officer_commander',
            darkmatterWeek: 3000,
            darkmatterMonth: 10000,
            imgBig: 'commander_big.png',
            imgSmall: 'commander_small.png',
        );
    }

    private function createDummyProduction(): ProductionFormula
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
}
