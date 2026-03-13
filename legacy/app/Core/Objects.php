<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObject;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Officer;
use App\Core\GameObjects\Ship;

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
        $this->objectsList = [
            'resources' => [1, 2, 3, 4, 12, 22, 23, 24],
            'facilities' => [14, 15, 21, 31, 33, 34, 41, 42, 43, 44],
            'defenses' => [401, 402, 403, 404, 405, 406, 407, 408],
            'missiles' => [502, 503],
            'build' => [1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 41, 42, 43, 44],
            'tech' => [106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 199],
            'fleet' => [202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215],
            'defense' => [401, 402, 403, 404, 405, 406, 407, 408, 502, 503],
            'officier' => [601, 602, 603, 604, 605],
            'prod' => [1, 2, 3, 4, 12, 212],
        ];
    }

    private function buildProduction(): void
    {
        $this->production = [
            1 => ['metal' => 40, 'crystal' => 10, 'deuterium' => 0, 'energy' => 0, 'factor' => 3 / 2,
                'formule' => [
                    'metal' => 'return (30 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
                    'crystal' => 'return "0";',
                    'deuterium' => 'return "0";',
                    'energy' => 'return - (10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'],
            ],
            2 => ['metal' => 30, 'crystal' => 15, 'deuterium' => 0, 'energy' => 0, 'factor' => 1.6,
                'formule' => [
                    'metal' => 'return "0";',
                    'crystal' => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);',
                    'deuterium' => 'return "0";',
                    'energy' => 'return - (10 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'],
            ],
            3 => ['metal' => 150, 'crystal' => 50, 'deuterium' => 0, 'energy' => 0, 'factor' => 3 / 2,
                'formule' => [
                    'metal' => 'return "0";',
                    'crystal' => 'return "0";',
                    'deuterium' => 'return ((10 * $BuildLevel * pow((1.1), $BuildLevel)) * (1.44 - 0.004 * $BuildTemp))  * (0.1 * $BuildLevelFactor);',
                    'energy' => 'return - floor(20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'],
            ],
            4 => ['metal' => 50, 'crystal' => 20, 'deuterium' => 0, 'energy' => 0, 'factor' => 3 / 2,
                'formule' => [
                    'metal' => 'return "0";',
                    'crystal' => 'return "0";',
                    'deuterium' => 'return "0";',
                    'energy' => 'return (20 * $BuildLevel * pow((1.1), $BuildLevel)) * (0.1 * $BuildLevelFactor);'],
            ],
            12 => ['metal' => 500, 'crystal' => 200, 'deuterium' => 100, 'energy' => 0, 'factor' => 1.8,
                'formule' => [
                    'metal' => 'return "0";',
                    'crystal' => 'return "0";',
                    'deuterium' => 'return - (10 * $BuildLevel * pow(1.1,$BuildLevel) * (0.1 * $BuildLevelFactor));',
                    'energy' => 'return (30 * $BuildLevel * pow((1.05 + $BuildEnergy * 0.01), $BuildLevel)) * (0.1 * $BuildLevelFactor);'],
            ],
            212 => ['metal' => 0, 'crystal' => 2000, 'deuterium' => 500, 'energy' => 0, 'factor' => 0.5,
                'formule' => [
                    'metal' => 'return "0";',
                    'crystal' => 'return "0";',
                    'deuterium' => 'return "0";',
                    'energy' => 'return ((($BuildTemp + 140) / 6) * (0.1 * $BuildLevelFactor) * $BuildLevel);'],
            ],
        ];
    }
}
