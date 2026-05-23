<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObject;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Officer;
use App\Core\GameObjects\Ship;

/**
 * @deprecated Use GameObjectRegistry instead
 */
class Objects
{
    private static ?Objects $instance = null;
    private array $objects = [];
    private array $relations = [];
    private array $price = [];
    private array $combatSpecs = [];
    private array $production = [];
    private array $objectsList = [];
    private GameObjectRegistry $registry;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Objects();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->registry = new GameObjectRegistry();

        $this->buildNames();
        $this->buildRelations();
        $this->buildPrices();
        $this->buildCombatSpecs();
        $this->buildObjectsList();
        $this->buildProduction();
    }

    public function registry(): GameObjectRegistry
    {
        return $this->registry;
    }

    public function getObjects(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->objects[$objectId];
        } else {
            return $this->objects;
        }
    }

    public function getRelations(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->relations[$objectId];
        } else {
            return $this->relations;
        }
    }

    public function getPrice(?int $objectId = null, string $resource = '')
    {
        if (!empty($objectId)) {
            if (empty($resource)) {
                return $this->price[$objectId];
            } else {
                return $this->price[$objectId][$resource];
            }
        } else {
            return $this->price;
        }
    }

    public function getCombatSpecs(?int $objectId = null, string $type = '')
    {
        if (!empty($objectId)) {
            if (empty($type)) {
                return $this->combatSpecs[$objectId];
            } else {
                return $this->combatSpecs[$objectId][$type];
            }
        } else {
            return $this->combatSpecs;
        }
    }

    public function getProduction(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->production[$objectId];
        } else {
            return $this->production;
        }
    }

    public function getObjectsList(?string $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->objectsList[$objectId];
        } else {
            return $this->objectsList;
        }
    }

    private function buildNames(): void
    {
        foreach ($this->registry->all() as $id => $obj) {
            $this->objects[$id] = $obj->getName();
        }
    }

    private function buildRelations(): void
    {
        foreach ($this->registry->all() as $id => $obj) {
            if ($obj instanceof GameObject && $obj->getRequirements()->isNotEmpty()) {
                $this->relations[$id] = $obj->getRequirements()->toArray();
            }
        }
    }

    private function buildPrices(): void
    {
        foreach ($this->registry->all() as $id => $obj) {
            if ($obj instanceof Officer) {
                $this->price[$id] = [
                    'darkmatter_week' => $obj->getDarkmatterWeek(),
                    'darkmatter_month' => $obj->getDarkmatterMonth(),
                    'img_big' => $obj->getImgBig(),
                    'img_small' => $obj->getImgSmall(),
                ];
                continue;
            }

            if (!$obj instanceof GameObject) {
                continue;
            }

            $this->price[$id] = $obj->getPrice()->toArray();

            if ($obj instanceof Ship) {
                $this->price[$id]['consumption'] = $obj->getConsumption();
                $this->price[$id]['consumption2'] = $obj->getConsumption2();
                $this->price[$id]['speed'] = $obj->getSpeed();
                $this->price[$id]['speed2'] = $obj->getSpeed2();
                $this->price[$id]['capacity'] = $obj->getCapacity();
            }
        }
    }

    private function buildCombatSpecs(): void
    {
        foreach ($this->registry->ships() as $id => $ship) {
            $this->combatSpecs[$id] = [
                'shield' => $ship->getShield(),
                'attack' => $ship->getAttack(),
                'sd' => $ship->getRapidFire()->toArray(),
            ];
        }

        foreach ($this->registry->defenses() as $id => $defense) {
            $this->combatSpecs[$id] = [
                'shield' => $defense->getShield(),
                'attack' => $defense->getAttack(),
            ];

            if ($defense->getRapidFire()->isNotEmpty()) {
                $this->combatSpecs[$id]['sd'] = $defense->getRapidFire()->toArray();
            }
        }
    }

    private function buildObjectsList(): void
    {
        $resourceIds = $this->registry->resourceBuildings()->keys()->toArray();
        $facilityIds = $this->registry->facilityBuildings()->keys()->toArray();
        $moonIds = $this->registry->moonBuildings()->keys()->toArray();
        $defenseIds = $this->registry->defenses()
            ->filter(fn (Defense $d) => $d->getId() < 500)
            ->keys()->toArray();
        $missileIds = $this->registry->defenses()
            ->filter(fn (Defense $d) => $d->getId() >= 500)
            ->keys()->toArray();
        $allBuildingIds = $this->registry->buildings()->keys()->toArray();
        $techIds = $this->registry->research()->keys()->toArray();
        $fleetIds = $this->registry->ships()->keys()->toArray();
        $allDefenseIds = $this->registry->defenses()->keys()->toArray();
        $officierIds = $this->registry->officers()->keys()->toArray();
        $prodIds = $this->registry->producers()->keys()->toArray();

        $this->objectsList = [
            'resources' => $resourceIds,
            'facilities' => array_merge($facilityIds, $moonIds),
            'defenses' => $defenseIds,
            'missiles' => $missileIds,
            'build' => $allBuildingIds,
            'tech' => $techIds,
            'fleet' => $fleetIds,
            'defense' => $allDefenseIds,
            'officier' => $officierIds,
            'prod' => $prodIds,
        ];
    }

    private function buildProduction(): void
    {
        foreach ($this->registry->producers() as $id => $obj) {
            $production = null;

            if ($obj instanceof Building) {
                $production = $obj->getProduction();
            } elseif ($obj instanceof Ship) {
                $production = $obj->getProduction();
            }

            if ($production === null) {
                continue;
            }

            $this->production[$id] = [
                'metal' => $production->getBaseMetal(),
                'crystal' => $production->getBaseCrystal(),
                'deuterium' => $production->getBaseDeuterium(),
                'energy' => 0,
                'factor' => $production->getFactor(),
                'formule' => [
                    'metal' => fn (int | float | string $buildLevel, int | float | string $buildLevelFactor, int | float | string $buildTemp = 0, int | float | string $buildEnergy = 0): float
                        => $production->calculateMetal((int) $buildLevel, (float) $buildLevelFactor),
                    'crystal' => fn (int | float | string $buildLevel, int | float | string $buildLevelFactor, int | float | string $buildTemp = 0, int | float | string $buildEnergy = 0): float
                        => $production->calculateCrystal((int) $buildLevel, (float) $buildLevelFactor),
                    'deuterium' => fn (int | float | string $buildLevel, int | float | string $buildLevelFactor, int | float | string $buildTemp = 0, int | float | string $buildEnergy = 0): float
                        => $production->calculateDeuterium((int) $buildLevel, (float) $buildLevelFactor, (float) $buildTemp),
                    'energy' => fn (int | float | string $buildLevel, int | float | string $buildLevelFactor, int | float | string $buildTemp = 0, int | float | string $buildEnergy = 0): float
                        => $production->calculateEnergy((int) $buildLevel, (float) $buildLevelFactor, (float) $buildTemp, (int) $buildEnergy),
                ],
            ];
        }
    }
}
