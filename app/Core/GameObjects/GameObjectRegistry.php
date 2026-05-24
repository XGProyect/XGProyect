<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use App\Enums\Game\BuildingCategory;
use App\Enums\Game\DriveType;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class GameObjectRegistry
{
    /** @var Collection<int, GameObjectInterface> */
    private Collection $objects;

    public function __construct()
    {
        $this->objects = new Collection();

        $this->registerBuildings();
        $this->registerResearch();
        $this->registerShips();
        $this->registerDefenses();
        $this->registerOfficers();
    }

    public function get(int $id): GameObjectInterface
    {
        /** @var GameObjectInterface|null $object */
        $object = $this->objects->get($id);

        if ($object === null) {
            throw new InvalidArgumentException("Game object with ID {$id} not found.");
        }

        return $object;
    }

    public function has(int $id): bool
    {
        return $this->objects->has($id);
    }

    /**
     * @return Collection<int, GameObjectInterface>
     */
    public function all(): Collection
    {
        return $this->objects;
    }

    /**
     * @return Collection<int, Ship>
     */
    public function ships(): Collection
    {
        return $this->objects->filter(fn ($obj) => $obj instanceof Ship);
    }

    /**
     * @return Collection<int, Building>
     */
    public function buildings(): Collection
    {
        return $this->objects->filter(fn ($obj) => $obj instanceof Building);
    }

    /**
     * @return Collection<int, Research>
     */
    public function research(): Collection
    {
        return $this->objects->filter(fn ($obj) => $obj instanceof Research);
    }

    /**
     * @return Collection<int, Defense>
     */
    public function defenses(): Collection
    {
        return $this->objects->filter(fn ($obj) => $obj instanceof Defense);
    }

    /**
     * @return Collection<int, Officer>
     */
    public function officers(): Collection
    {
        return $this->objects->filter(fn ($obj) => $obj instanceof Officer);
    }

    /**
     * Buildings in the "resource" category (mines, energy plants, storage).
     *
     * @return Collection<int, Building>
     */
    public function resourceBuildings(): Collection
    {
        return $this->buildings()->filter(fn (Building $b) => $b->getCategory() === BuildingCategory::Resource);
    }

    /**
     * Buildings in the "facility" category (factories, shipyard, lab, etc.).
     *
     * @return Collection<int, Building>
     */
    public function facilityBuildings(): Collection
    {
        return $this->buildings()->filter(fn (Building $b) => $b->getCategory() === BuildingCategory::Facility);
    }

    /**
     * Buildings exclusive to moons (lunar base, phalanx, jump gate).
     *
     * @return Collection<int, Building>
     */
    public function moonBuildings(): Collection
    {
        return $this->buildings()->filter(fn (Building $b) => $b->getCategory() === BuildingCategory::Moon);
    }

    /**
     * All objects that have production formulas (buildings + solar satellite).
     *
     * @return Collection<int, Building|Ship>
     */
    public function producers(): Collection
    {
        return $this->objects->filter(
            fn ($obj) => ($obj instanceof Building && $obj->hasProduction()) ||
                ($obj instanceof Ship && $obj->hasProduction())
        );
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    private function registerBuildings(): void
    {
        $this->objects[1] = new Building(
            id: 1,
            name: 'building_metal_mine',
            price: new Price(metal: 60, crystal: 15, factor: 1.5),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
            production: new ProductionFormula(
                baseMetal: 40,
                baseCrystal: 10,
                baseDeuterium: 0,
                factor: 1.5,
                metalFormula: fn (int $level, float $levelFactor): float
                    => (30 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
                crystalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => 0,
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => -(10 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
            ),
        );

        $this->objects[2] = new Building(
            id: 2,
            name: 'building_crystal_mine',
            price: new Price(metal: 48, crystal: 24, factor: 1.6),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
            production: new ProductionFormula(
                baseMetal: 30,
                baseCrystal: 15,
                baseDeuterium: 0,
                factor: 1.6,
                metalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                crystalFormula: fn (int $level, float $levelFactor): float
                    => (20 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => 0,
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => -(10 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
            ),
        );

        $this->objects[3] = new Building(
            id: 3,
            name: 'building_deuterium_sintetizer',
            price: new Price(metal: 225, crystal: 75, factor: 1.5),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
            production: new ProductionFormula(
                baseMetal: 150,
                baseCrystal: 50,
                baseDeuterium: 0,
                factor: 1.5,
                metalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                crystalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => ((10 * $level * pow(1.1, $level)) * (1.44 - 0.004 * $planetTemp)) * (0.1 * $levelFactor),
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => -floor(20 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
            ),
        );

        $this->objects[4] = new Building(
            id: 4,
            name: 'building_solar_plant',
            price: new Price(metal: 75, crystal: 30, factor: 1.5),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
            production: new ProductionFormula(
                baseMetal: 50,
                baseCrystal: 20,
                baseDeuterium: 0,
                factor: 1.5,
                metalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                crystalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => 0,
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => (20 * $level * pow(1.1, $level)) * (0.1 * $levelFactor),
            ),
        );

        $this->objects[12] = new Building(
            id: 12,
            name: 'building_fusion_reactor',
            price: new Price(metal: 900, crystal: 360, deuterium: 180, factor: 1.8),
            requirements: new Collection([3 => 5, 113 => 3]),
            category: BuildingCategory::Resource,
            production: new ProductionFormula(
                baseMetal: 500,
                baseCrystal: 200,
                baseDeuterium: 100,
                factor: 1.8,
                metalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                crystalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => -(10 * $level * pow(1.1, $level) * (0.1 * $levelFactor)),
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => (30 * $level * pow((1.05 + $energyTech * 0.01), $level)) * (0.1 * $levelFactor),
            ),
        );

        $this->objects[14] = new Building(
            id: 14,
            name: 'building_robot_factory',
            price: new Price(metal: 400, crystal: 120, deuterium: 200, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Facility,
        );

        $this->objects[15] = new Building(
            id: 15,
            name: 'building_nano_factory',
            price: new Price(metal: 1000000, crystal: 500000, deuterium: 100000, factor: 2),
            requirements: new Collection([14 => 10, 108 => 10]),
            category: BuildingCategory::Facility,
        );

        $this->objects[21] = new Building(
            id: 21,
            name: 'building_hangar',
            price: new Price(metal: 400, crystal: 200, deuterium: 100, factor: 2),
            requirements: new Collection([14 => 2]),
            category: BuildingCategory::Facility,
        );

        $this->objects[22] = new Building(
            id: 22,
            name: 'building_metal_store',
            price: new Price(metal: 1000, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
        );

        $this->objects[23] = new Building(
            id: 23,
            name: 'building_crystal_store',
            price: new Price(metal: 1000, crystal: 500, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
        );

        $this->objects[24] = new Building(
            id: 24,
            name: 'building_deuterium_tank',
            price: new Price(metal: 1000, crystal: 1000, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Resource,
        );

        $this->objects[31] = new Building(
            id: 31,
            name: 'building_laboratory',
            price: new Price(metal: 200, crystal: 400, deuterium: 200, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Facility,
        );

        $this->objects[33] = new Building(
            id: 33,
            name: 'building_terraformer',
            price: new Price(crystal: 50000, deuterium: 100000, energyMax: 1000, factor: 2),
            requirements: new Collection([15 => 1, 113 => 12]),
            category: BuildingCategory::Facility,
        );

        $this->objects[34] = new Building(
            id: 34,
            name: 'building_ally_deposit',
            price: new Price(metal: 20000, crystal: 40000, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Facility,
        );

        $this->objects[41] = new Building(
            id: 41,
            name: 'building_mondbasis',
            price: new Price(metal: 20000, crystal: 40000, deuterium: 20000, factor: 2),
            requirements: new Collection(),
            category: BuildingCategory::Moon,
        );

        $this->objects[42] = new Building(
            id: 42,
            name: 'building_phalanx',
            price: new Price(metal: 20000, crystal: 40000, deuterium: 20000, factor: 2),
            requirements: new Collection([41 => 1]),
            category: BuildingCategory::Moon,
        );

        $this->objects[43] = new Building(
            id: 43,
            name: 'building_jump_gate',
            price: new Price(metal: 2000000, crystal: 4000000, deuterium: 2000000, factor: 2),
            requirements: new Collection([41 => 1, 114 => 7]),
            category: BuildingCategory::Moon,
        );

        $this->objects[44] = new Building(
            id: 44,
            name: 'building_missile_silo',
            price: new Price(metal: 20000, crystal: 20000, deuterium: 1000, factor: 2),
            requirements: new Collection([21 => 1]),
            category: BuildingCategory::Facility,
        );
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    private function registerResearch(): void
    {
        $this->objects[106] = new Research(
            id: 106,
            name: 'research_espionage_technology',
            price: new Price(metal: 200, crystal: 1000, deuterium: 200, factor: 2),
            requirements: new Collection([31 => 3]),
        );

        $this->objects[108] = new Research(
            id: 108,
            name: 'research_computer_technology',
            price: new Price(crystal: 400, deuterium: 600, factor: 2),
            requirements: new Collection([31 => 1]),
        );

        $this->objects[109] = new Research(
            id: 109,
            name: 'research_weapons_technology',
            price: new Price(metal: 800, crystal: 200, factor: 2),
            requirements: new Collection([31 => 4]),
        );

        $this->objects[110] = new Research(
            id: 110,
            name: 'research_shielding_technology',
            price: new Price(metal: 200, crystal: 600, factor: 2),
            requirements: new Collection([31 => 6, 113 => 3]),
        );

        $this->objects[111] = new Research(
            id: 111,
            name: 'research_armour_technology',
            price: new Price(metal: 1000, factor: 2),
            requirements: new Collection([31 => 2]),
        );

        $this->objects[113] = new Research(
            id: 113,
            name: 'research_energy_technology',
            price: new Price(crystal: 800, deuterium: 400, factor: 2),
            requirements: new Collection([31 => 1]),
        );

        $this->objects[114] = new Research(
            id: 114,
            name: 'research_hyperspace_technology',
            price: new Price(crystal: 4000, deuterium: 2000, factor: 2),
            requirements: new Collection([31 => 7, 113 => 5, 110 => 5]),
        );

        $this->objects[115] = new Research(
            id: 115,
            name: 'research_combustion_drive',
            price: new Price(metal: 400, deuterium: 600, factor: 2),
            requirements: new Collection([31 => 1, 113 => 1]),
        );

        $this->objects[117] = new Research(
            id: 117,
            name: 'research_impulse_drive',
            price: new Price(metal: 2000, crystal: 4000, deuterium: 600, factor: 2),
            requirements: new Collection([31 => 2, 113 => 1]),
        );

        $this->objects[118] = new Research(
            id: 118,
            name: 'research_hyperspace_drive',
            price: new Price(metal: 10000, crystal: 20000, deuterium: 6000, factor: 2),
            requirements: new Collection([31 => 7, 114 => 3]),
        );

        $this->objects[120] = new Research(
            id: 120,
            name: 'research_laser_technology',
            price: new Price(metal: 200, crystal: 100, factor: 2),
            requirements: new Collection([31 => 1, 113 => 2]),
        );

        $this->objects[121] = new Research(
            id: 121,
            name: 'research_ionic_technology',
            price: new Price(metal: 1000, crystal: 300, deuterium: 100, factor: 2),
            requirements: new Collection([31 => 4, 113 => 4, 120 => 5]),
        );

        $this->objects[122] = new Research(
            id: 122,
            name: 'research_plasma_technology',
            price: new Price(metal: 2000, crystal: 4000, deuterium: 1000, factor: 2),
            requirements: new Collection([31 => 5, 113 => 8, 120 => 10, 121 => 5]),
        );

        $this->objects[123] = new Research(
            id: 123,
            name: 'research_intergalactic_research_network',
            price: new Price(metal: 240000, crystal: 400000, deuterium: 160000, factor: 2),
            requirements: new Collection([31 => 10, 108 => 8, 114 => 8]),
        );

        $this->objects[124] = new Research(
            id: 124,
            name: 'research_astrophysics',
            price: new Price(metal: 4000, crystal: 8000, deuterium: 4000, factor: 1.75),
            requirements: new Collection([31 => 3, 106 => 4, 117 => 3]),
        );

        $this->objects[199] = new Research(
            id: 199,
            name: 'research_graviton_technology',
            price: new Price(energyMax: 300000, factor: 3),
            requirements: new Collection([31 => 12]),
        );
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    private function registerShips(): void
    {
        $this->objects[202] = new Ship(
            id: 202,
            name: 'ship_small_cargo_ship',
            price: new Price(metal: 2000, crystal: 2000, factor: 1),
            requirements: new Collection([21 => 2, 115 => 2]),
            shield: 10,
            attack: 5,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 5000,
            speed2: 10000,
            consumption: 10,
            consumption2: 20,
            capacity: 5000,
            drive: new DriveSpec(
                primary: DriveType::Combustion,
                secondary: DriveType::Impulse,
                secondaryMinLevel: 5,
            ),
        );

        $this->objects[203] = new Ship(
            id: 203,
            name: 'ship_big_cargo_ship',
            price: new Price(metal: 6000, crystal: 6000, factor: 1),
            requirements: new Collection([21 => 4, 115 => 6]),
            shield: 25,
            attack: 5,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 7500,
            speed2: 7500,
            consumption: 50,
            consumption2: 50,
            capacity: 25000,
            drive: new DriveSpec(primary: DriveType::Combustion),
        );

        $this->objects[204] = new Ship(
            id: 204,
            name: 'ship_light_fighter',
            price: new Price(metal: 3000, crystal: 1000, factor: 1),
            requirements: new Collection([21 => 1, 115 => 1]),
            shield: 10,
            attack: 50,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 12500,
            speed2: 12500,
            consumption: 20,
            consumption2: 20,
            capacity: 50,
            drive: new DriveSpec(primary: DriveType::Combustion),
        );

        $this->objects[205] = new Ship(
            id: 205,
            name: 'ship_heavy_fighter',
            price: new Price(metal: 6000, crystal: 4000, factor: 1),
            requirements: new Collection([21 => 3, 111 => 2, 117 => 2]),
            shield: 25,
            attack: 150,
            rapidFire: new Collection([202 => 3, 210 => 5, 212 => 5]),
            speed: 10000,
            speed2: 10000,
            consumption: 75,
            consumption2: 75,
            capacity: 100,
            drive: new DriveSpec(primary: DriveType::Impulse),
        );

        $this->objects[206] = new Ship(
            id: 206,
            name: 'ship_cruiser',
            price: new Price(metal: 20000, crystal: 7000, deuterium: 2000, factor: 1),
            requirements: new Collection([21 => 5, 117 => 4, 121 => 2]),
            shield: 50,
            attack: 400,
            rapidFire: new Collection([204 => 6, 210 => 5, 212 => 5, 401 => 10]),
            speed: 15000,
            speed2: 15000,
            consumption: 300,
            consumption2: 300,
            capacity: 800,
            drive: new DriveSpec(primary: DriveType::Impulse),
        );

        $this->objects[207] = new Ship(
            id: 207,
            name: 'ship_battleship',
            price: new Price(metal: 45000, crystal: 15000, factor: 1),
            requirements: new Collection([21 => 7, 118 => 4]),
            shield: 200,
            attack: 1000,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 10000,
            speed2: 10000,
            consumption: 500,
            consumption2: 500,
            capacity: 1500,
            drive: new DriveSpec(primary: DriveType::Hyperspace),
        );

        $this->objects[208] = new Ship(
            id: 208,
            name: 'ship_colony_ship',
            price: new Price(metal: 10000, crystal: 20000, deuterium: 10000, factor: 1),
            requirements: new Collection([21 => 4, 117 => 3]),
            shield: 100,
            attack: 50,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 2500,
            speed2: 2500,
            consumption: 1000,
            consumption2: 1000,
            capacity: 7500,
            drive: new DriveSpec(primary: DriveType::Impulse),
        );

        $this->objects[209] = new Ship(
            id: 209,
            name: 'ship_recycler',
            price: new Price(metal: 10000, crystal: 6000, deuterium: 2000, factor: 1),
            requirements: new Collection([21 => 4, 115 => 6, 110 => 2]),
            shield: 10,
            attack: 1,
            rapidFire: new Collection([210 => 5, 212 => 5]),
            speed: 2000,
            speed2: 2000,
            consumption: 300,
            consumption2: 300,
            capacity: 20000,
            drive: new DriveSpec(
                primary: DriveType::Combustion,
                secondary: DriveType::Impulse,
                secondaryMinLevel: 17,
                tertiary: DriveType::Hyperspace,
                tertiaryMinLevel: 15,
            ),
        );

        $this->objects[210] = new Ship(
            id: 210,
            name: 'ship_espionage_probe',
            price: new Price(crystal: 1000, factor: 1),
            requirements: new Collection([21 => 3, 115 => 3, 106 => 2]),
            shield: 0.01,
            attack: 0.01,
            rapidFire: new Collection(),
            speed: 100000000,
            speed2: 100000000,
            consumption: 1,
            consumption2: 1,
            capacity: 5,
            drive: new DriveSpec(primary: DriveType::Combustion),
        );

        $this->objects[211] = new Ship(
            id: 211,
            name: 'ship_bomber',
            price: new Price(metal: 50000, crystal: 25000, deuterium: 15000, factor: 1),
            requirements: new Collection([21 => 8, 117 => 6, 122 => 5]),
            shield: 500,
            attack: 1000,
            rapidFire: new Collection([210 => 5, 212 => 5, 401 => 20, 402 => 20, 403 => 10, 404 => 5, 405 => 10, 406 => 5]),
            speed: 4000,
            speed2: 5000,
            consumption: 700,
            consumption2: 700,
            capacity: 500,
            drive: new DriveSpec(
                primary: DriveType::Impulse,
                secondary: DriveType::Hyperspace,
                secondaryMinLevel: 8,
            ),
        );

        $this->objects[212] = new Ship(
            id: 212,
            name: 'ship_solar_satellite',
            price: new Price(crystal: 2000, deuterium: 500, factor: 1),
            requirements: new Collection([21 => 1]),
            shield: 1,
            attack: 1,
            rapidFire: new Collection([210 => 1]),
            speed: 0,
            speed2: 0,
            consumption: 0,
            consumption2: 0,
            capacity: 0,
            drive: new DriveSpec(primary: DriveType::None),
            production: new ProductionFormula(
                baseMetal: 0,
                baseCrystal: 2000,
                baseDeuterium: 500,
                factor: 0.5,
                metalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                crystalFormula: fn (int $level, float $levelFactor): float
                    => 0,
                deuteriumFormula: fn (int $level, float $levelFactor, float $planetTemp): float
                    => 0,
                energyFormula: fn (int $level, float $levelFactor, float $planetTemp, int $energyTech): float
                    => (($planetTemp + 140) / 6) * (0.1 * $levelFactor) * $level,
            ),
        );

        $this->objects[213] = new Ship(
            id: 213,
            name: 'ship_destroyer',
            price: new Price(metal: 60000, crystal: 50000, deuterium: 15000, factor: 1),
            requirements: new Collection([21 => 9, 114 => 5, 118 => 6]),
            shield: 500,
            attack: 2000,
            rapidFire: new Collection([210 => 5, 212 => 5, 215 => 2, 402 => 10]),
            speed: 5000,
            speed2: 5000,
            consumption: 1000,
            consumption2: 1000,
            capacity: 2000,
            drive: new DriveSpec(primary: DriveType::Hyperspace),
        );

        $this->objects[214] = new Ship(
            id: 214,
            name: 'ship_deathstar',
            price: new Price(metal: 5000000, crystal: 4000000, deuterium: 1000000, factor: 1),
            requirements: new Collection([21 => 12, 114 => 6, 118 => 7, 199 => 1]),
            shield: 50000,
            attack: 200000,
            rapidFire: new Collection([202 => 250, 203 => 250, 204 => 200, 205 => 100, 206 => 33, 207 => 30, 208 => 250, 209 => 250, 210 => 1250, 211 => 25, 212 => 1250, 213 => 5, 215 => 15, 401 => 200, 402 => 200, 403 => 100, 404 => 50, 405 => 100]),
            speed: 100,
            speed2: 100,
            consumption: 1,
            consumption2: 1,
            capacity: 1000000,
            drive: new DriveSpec(primary: DriveType::Hyperspace),
        );

        $this->objects[215] = new Ship(
            id: 215,
            name: 'ship_reaper',
            price: new Price(metal: 30000, crystal: 40000, deuterium: 15000, factor: 1),
            requirements: new Collection([21 => 8, 114 => 5, 118 => 5, 120 => 12]),
            shield: 400,
            attack: 700,
            rapidFire: new Collection([202 => 3, 203 => 3, 205 => 4, 206 => 4, 207 => 7, 210 => 5, 212 => 5]),
            speed: 10000,
            speed2: 10000,
            consumption: 250,
            consumption2: 250,
            capacity: 750,
            drive: new DriveSpec(primary: DriveType::Hyperspace),
        );
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    private function registerDefenses(): void
    {
        $this->objects[401] = new Defense(
            id: 401,
            name: 'defense_rocket_launcher',
            price: new Price(metal: 2000, factor: 1),
            requirements: new Collection([21 => 1]),
            shield: 20,
            attack: 80,
            rapidFire: new Collection(),
        );

        $this->objects[402] = new Defense(
            id: 402,
            name: 'defense_light_laser',
            price: new Price(metal: 1500, crystal: 500, factor: 1),
            requirements: new Collection([21 => 2, 113 => 1, 120 => 3]),
            shield: 25,
            attack: 100,
            rapidFire: new Collection(),
        );

        $this->objects[403] = new Defense(
            id: 403,
            name: 'defense_heavy_laser',
            price: new Price(metal: 6000, crystal: 2000, factor: 1),
            requirements: new Collection([21 => 4, 113 => 3, 120 => 6]),
            shield: 100,
            attack: 250,
            rapidFire: new Collection(),
        );

        $this->objects[404] = new Defense(
            id: 404,
            name: 'defense_gauss_cannon',
            price: new Price(metal: 20000, crystal: 15000, deuterium: 2000, factor: 1),
            requirements: new Collection([21 => 6, 113 => 6, 109 => 3, 110 => 1]),
            shield: 200,
            attack: 1100,
            rapidFire: new Collection(),
        );

        $this->objects[405] = new Defense(
            id: 405,
            name: 'defense_ion_cannon',
            price: new Price(metal: 2000, crystal: 6000, factor: 1),
            requirements: new Collection([21 => 4, 121 => 4]),
            shield: 500,
            attack: 150,
            rapidFire: new Collection(),
        );

        $this->objects[406] = new Defense(
            id: 406,
            name: 'defense_plasma_turret',
            price: new Price(metal: 50000, crystal: 50000, deuterium: 30000, factor: 1),
            requirements: new Collection([21 => 8, 122 => 7]),
            shield: 300,
            attack: 3000,
            rapidFire: new Collection(),
        );

        $this->objects[407] = new Defense(
            id: 407,
            name: 'defense_small_shield_dome',
            price: new Price(metal: 10000, crystal: 10000, factor: 1),
            requirements: new Collection([21 => 1, 110 => 2]),
            shield: 2000,
            attack: 1,
            rapidFire: new Collection(),
        );

        $this->objects[408] = new Defense(
            id: 408,
            name: 'defense_large_shield_dome',
            price: new Price(metal: 50000, crystal: 50000, factor: 1),
            requirements: new Collection([21 => 6, 110 => 6]),
            shield: 10000,
            attack: 1,
            rapidFire: new Collection(),
        );

        $this->objects[502] = new Defense(
            id: 502,
            name: 'defense_anti-ballistic_missile',
            price: new Price(metal: 8000, deuterium: 2000, factor: 1),
            requirements: new Collection([21 => 1, 44 => 2]),
            shield: 1,
            attack: 1,
            rapidFire: new Collection(),
        );

        $this->objects[503] = new Defense(
            id: 503,
            name: 'defense_interplanetary_missile',
            price: new Price(metal: 12500, crystal: 2500, deuterium: 10000, factor: 1),
            requirements: new Collection([21 => 1, 44 => 4, 117 => 1]),
            shield: 1,
            attack: 12000,
            rapidFire: new Collection(),
        );
    }

    private function registerOfficers(): void
    {
        $this->objects[601] = new Officer(
            id: 601,
            name: 'premium_officier_commander',
            darkmatterWeek: 10000,
            darkmatterMonth: 100000,
            imgBig: 'commander_stern_gross',
            imgSmall: 'commander_icon',
        );

        $this->objects[602] = new Officer(
            id: 602,
            name: 'premium_officier_admiral',
            darkmatterWeek: 5000,
            darkmatterMonth: 50000,
            imgBig: 'ogame_admiral',
            imgSmall: 'admiral_icon',
        );

        $this->objects[603] = new Officer(
            id: 603,
            name: 'premium_officier_engineer',
            darkmatterWeek: 5000,
            darkmatterMonth: 50000,
            imgBig: 'ogame_ingenieur',
            imgSmall: 'engineer_icon',
        );

        $this->objects[604] = new Officer(
            id: 604,
            name: 'premium_officier_geologist',
            darkmatterWeek: 12500,
            darkmatterMonth: 125000,
            imgBig: 'ogame_geologe',
            imgSmall: 'geologist_icon',
        );

        $this->objects[605] = new Officer(
            id: 605,
            name: 'premium_officier_technocrat',
            darkmatterWeek: 10000,
            darkmatterMonth: 100000,
            imgBig: 'ogame_technokrat',
            imgSmall: 'technocrat_icon',
        );
    }
}
