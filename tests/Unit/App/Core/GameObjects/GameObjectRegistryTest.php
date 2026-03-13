<?php

declare(strict_types=1);

namespace Tests\Unit\App\Core\GameObjects;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Officer;
use App\Core\GameObjects\Research;
use App\Core\GameObjects\Ship;
use App\Enums\Game\BuildingCategory;
use App\Enums\Game\DriveType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class GameObjectRegistryTest extends TestCase
{
    private GameObjectRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new GameObjectRegistry();
    }

    // ── Total counts ─────────────────────────────────────────────────

    public function testTotalObjectCount(): void
    {
        // 18 buildings + 16 research + 14 ships + 10 defenses + 5 officers = 63
        $this->assertCount(63, $this->registry->all());
    }

    public function testBuildingCount(): void
    {
        $this->assertCount(18, $this->registry->buildings());
    }

    public function testResearchCount(): void
    {
        $this->assertCount(16, $this->registry->research());
    }

    public function testShipCount(): void
    {
        $this->assertCount(14, $this->registry->ships());
    }

    public function testDefenseCount(): void
    {
        $this->assertCount(10, $this->registry->defenses());
    }

    public function testOfficerCount(): void
    {
        $this->assertCount(5, $this->registry->officers());
    }

    // ── Category filters ─────────────────────────────────────────────

    public function testResourceBuildingCount(): void
    {
        $this->assertCount(8, $this->registry->resourceBuildings());
    }

    public function testFacilityBuildingCount(): void
    {
        $this->assertCount(7, $this->registry->facilityBuildings());
    }

    public function testMoonBuildingCount(): void
    {
        $this->assertCount(3, $this->registry->moonBuildings());
    }

    public function testBuildingCategoriesSumToTotal(): void
    {
        $total = $this->registry->resourceBuildings()->count()
            + $this->registry->facilityBuildings()->count()
            + $this->registry->moonBuildings()->count();

        // Note: some buildings may appear in multiple categories or none
        // but in this design each building has exactly one category
        $this->assertSame($this->registry->buildings()->count(), $total);
    }

    public function testResourceBuildingsAllHaveResourceCategory(): void
    {
        foreach ($this->registry->resourceBuildings() as $building) {
            $this->assertSame(BuildingCategory::Resource, $building->getCategory());
        }
    }

    public function testFacilityBuildingsAllHaveFacilityCategory(): void
    {
        foreach ($this->registry->facilityBuildings() as $building) {
            $this->assertSame(BuildingCategory::Facility, $building->getCategory());
        }
    }

    public function testMoonBuildingsAllHaveMoonCategory(): void
    {
        foreach ($this->registry->moonBuildings() as $building) {
            $this->assertSame(BuildingCategory::Moon, $building->getCategory());
        }
    }

    // ── Producers ────────────────────────────────────────────────────

    public function testProducerCount(): void
    {
        // Metal mine(1), Crystal mine(2), Deuterium synthesizer(3),
        // Solar plant(4), Fusion reactor(12), Solar satellite(212)
        $this->assertCount(6, $this->registry->producers());
    }

    public function testProducerIds(): void
    {
        $ids = $this->registry->producers()->keys()->sort()->values()->toArray();

        $this->assertSame([1, 2, 3, 4, 12, 212], $ids);
    }

    public function testAllProducersHaveProductionFormulas(): void
    {
        foreach ($this->registry->producers() as $obj) {
            if ($obj instanceof Building) {
                $this->assertTrue($obj->hasProduction(), "Building {$obj->getId()} should have production");
            } elseif ($obj instanceof Ship) {
                $this->assertTrue($obj->hasProduction(), "Ship {$obj->getId()} should have production");
            }
        }
    }

    // ── get() and has() ──────────────────────────────────────────────

    public function testGetReturnsCorrectObject(): void
    {
        $obj = $this->registry->get(1);

        $this->assertInstanceOf(Building::class, $obj);
        $this->assertSame(1, $obj->getId());
        $this->assertSame('building_metal_mine', $obj->getName());
    }

    public function testHasReturnsTrueForExistingId(): void
    {
        $this->assertTrue($this->registry->has(1));
        $this->assertTrue($this->registry->has(202));
        $this->assertTrue($this->registry->has(601));
    }

    public function testHasReturnsFalseForMissingId(): void
    {
        $this->assertFalse($this->registry->has(999));
    }

    public function testGetThrowsForMissingId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->registry->get(999);
    }

    // ── Type-specific collections ────────────────────────────────────

    public function testAllBuildingsAreInstanceOfBuilding(): void
    {
        foreach ($this->registry->buildings() as $obj) {
            $this->assertInstanceOf(Building::class, $obj);
        }
    }

    public function testAllResearchAreInstanceOfResearch(): void
    {
        foreach ($this->registry->research() as $obj) {
            $this->assertInstanceOf(Research::class, $obj);
        }
    }

    public function testAllShipsAreInstanceOfShip(): void
    {
        foreach ($this->registry->ships() as $obj) {
            $this->assertInstanceOf(Ship::class, $obj);
        }
    }

    public function testAllDefensesAreInstanceOfDefense(): void
    {
        foreach ($this->registry->defenses() as $obj) {
            $this->assertInstanceOf(Defense::class, $obj);
        }
    }

    public function testAllOfficersAreInstanceOfOfficer(): void
    {
        foreach ($this->registry->officers() as $obj) {
            $this->assertInstanceOf(Officer::class, $obj);
        }
    }

    // ── ID ranges ────────────────────────────────────────────────────

    public function testBuildingIdRange(): void
    {
        foreach ($this->registry->buildings() as $id => $obj) {
            $this->assertGreaterThanOrEqual(1, $id);
            $this->assertLessThanOrEqual(44, $id);
            $this->assertSame($id, $obj->getId());
        }
    }

    public function testResearchIdRange(): void
    {
        foreach ($this->registry->research() as $id => $obj) {
            $this->assertGreaterThanOrEqual(106, $id);
            $this->assertLessThanOrEqual(199, $id);
            $this->assertSame($id, $obj->getId());
        }
    }

    public function testShipIdRange(): void
    {
        foreach ($this->registry->ships() as $id => $obj) {
            $this->assertGreaterThanOrEqual(202, $id);
            $this->assertLessThanOrEqual(215, $id);
            $this->assertSame($id, $obj->getId());
        }
    }

    public function testDefenseIdRange(): void
    {
        foreach ($this->registry->defenses() as $id => $obj) {
            $this->assertGreaterThanOrEqual(401, $id);
            $this->assertLessThanOrEqual(503, $id);
            $this->assertSame($id, $obj->getId());
        }
    }

    public function testOfficerIdRange(): void
    {
        foreach ($this->registry->officers() as $id => $obj) {
            $this->assertGreaterThanOrEqual(601, $id);
            $this->assertLessThanOrEqual(605, $id);
            $this->assertSame($id, $obj->getId());
        }
    }

    // ── All objects implement interface ──────────────────────────────

    public function testAllObjectsImplementGameObjectInterface(): void
    {
        foreach ($this->registry->all() as $obj) {
            $this->assertInstanceOf(GameObjectInterface::class, $obj);
        }
    }

    // ── Key-to-ID consistency ────────────────────────────────────────

    public function testCollectionKeysMatchObjectIds(): void
    {
        foreach ($this->registry->all() as $key => $obj) {
            $this->assertSame($key, $obj->getId(), "Collection key {$key} must match object ID {$obj->getId()}");
        }
    }

    // ── Specific object spot checks ──────────────────────────────────

    public function testMetalMineProduction(): void
    {
        /** @var Building $mine */
        $mine = $this->registry->get(1);

        $this->assertTrue($mine->hasProduction());

        $production = $mine->getProduction();
        $this->assertNotNull($production);

        // Level 1, factor 1.0: metal mine formula result
        $result = $production->calculateMetal(1, 1.0);
        $this->assertEqualsWithDelta(3.3, $result, 0.001);
    }

    public function testSolarSatelliteIsProducer(): void
    {
        /** @var Ship $satellite */
        $satellite = $this->registry->get(212);

        $this->assertInstanceOf(Ship::class, $satellite);
        $this->assertTrue($satellite->hasProduction());
    }

    public function testSmallCargoShipDriveIsUpgradeable(): void
    {
        /** @var Ship $cargo */
        $cargo = $this->registry->get(202);

        $drive = $cargo->getDrive();
        $this->assertSame(DriveType::Combustion, $drive->getPrimary());
        $this->assertSame(DriveType::Impulse, $drive->getSecondary());
        $this->assertSame(5, $drive->getSecondaryMinLevel());
    }

    public function testRecyclerHasThreeStageDrive(): void
    {
        /** @var Ship $recycler */
        $recycler = $this->registry->get(209);

        $drive = $recycler->getDrive();
        $this->assertSame(DriveType::Combustion, $drive->getPrimary());
        $this->assertSame(DriveType::Impulse, $drive->getSecondary());
        $this->assertSame(DriveType::Hyperspace, $drive->getTertiary());
        $this->assertNotNull($drive->getTertiaryMinLevel());
    }

    public function testCommanderOfficer(): void
    {
        /** @var Officer $commander */
        $commander = $this->registry->get(601);

        $this->assertInstanceOf(Officer::class, $commander);
        $this->assertSame('premium_officier_commander', $commander->getName());
        $this->assertSame(10000, $commander->getDarkmatterWeek());
        $this->assertSame(100000, $commander->getDarkmatterMonth());
    }

    // ── Requirements reference valid IDs ─────────────────────────────

    public function testAllRequirementsReferenceValidIds(): void
    {
        foreach ($this->registry->all() as $id => $obj) {
            if (!method_exists($obj, 'getRequirements')) {
                continue;
            }

            foreach ($obj->getRequirements() as $reqId => $reqLevel) {
                $this->assertTrue(
                    $this->registry->has($reqId),
                    "Object {$id} requires non-existent object {$reqId}"
                );
                $this->assertGreaterThan(0, $reqLevel, "Requirement level for {$reqId} on {$id} must be > 0");
            }
        }
    }

    // ── Rapid fire targets reference valid IDs ───────────────────────

    public function testAllRapidFireTargetsAreValidIds(): void
    {
        foreach ($this->registry->ships() as $id => $ship) {
            foreach ($ship->getRapidFire() as $targetId => $shots) {
                $this->assertTrue(
                    $this->registry->has($targetId),
                    "Ship {$id} has rapid fire against non-existent object {$targetId}"
                );
                $this->assertGreaterThan(0, $shots, "Rapid fire shots for target {$targetId} on ship {$id} must be > 0");
            }
        }

        foreach ($this->registry->defenses() as $id => $defense) {
            foreach ($defense->getRapidFire() as $targetId => $shots) {
                $this->assertTrue(
                    $this->registry->has($targetId),
                    "Defense {$id} has rapid fire against non-existent object {$targetId}"
                );
            }
        }
    }

    // ── Every ship has a valid drive ─────────────────────────────────

    public function testAllShipsHaveValidDrive(): void
    {
        foreach ($this->registry->ships() as $id => $ship) {
            $drive = $ship->getDrive();
            $this->assertInstanceOf(DriveType::class, $drive->getPrimary(), "Ship {$id} must have a primary drive");
        }
    }

    // ── Price factors ────────────────────────────────────────────────

    public function testAllObjectsHavePositivePriceFactor(): void
    {
        foreach ($this->registry->all() as $id => $obj) {
            if (!method_exists($obj, 'getPrice')) {
                continue;
            }

            $this->assertGreaterThan(
                0,
                $obj->getPrice()->getFactor(),
                "Object {$id} must have a positive price factor"
            );
        }
    }
}
