<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Research;
use App\Core\GameObjects\Ship;
use App\Enums\Game\BuildingCategory;

/**
 * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
 * @SuppressWarnings("PHPMD.ElseExpression")
 */
class DevelopmentsService
{
    public function __construct(
        private GameObjectRegistry $registry,
        private FormulasService $formulas,
    ) {
    }

    public function setBuildingPage(int $elementId): string
    {
        if (!$this->registry->has($elementId)) {
            return 'overview';
        }

        $object = $this->registry->get($elementId);

        if (!$object instanceof Building) {
            return 'overview';
        }

        return match ($object->getCategory()) {
            BuildingCategory::Resource => 'supplies',
            BuildingCategory::Facility, BuildingCategory::Moon => 'facilities',
        };
    }

    public function maxFields(int $planetFieldMax, int $terraformerLevel): int
    {
        return $planetFieldMax + ($terraformerLevel * FIELDS_BY_TERRAFORMER);
    }

    /**
     * Calculate the cost of a development at the given level.
     *
     * @return array<string, float> Resource type => cost
     */
    public function developmentPrice(int $elementId, int $currentLevel, bool $incremental = true, bool $destroy = false, int $ionTechLevel = 0): array
    {
        $price = $this->registry->get($elementId)->getPrice();
        $cost = [];
        $level = $incremental ? $currentLevel : 0;

        $resources = [
            'metal' => $price->getMetal(),
            'crystal' => $price->getCrystal(),
            'deuterium' => $price->getDeuterium(),
            'energy_max' => $price->getEnergyMax(),
        ];

        foreach ($resources as $type => $basePrice) {
            if ($basePrice > 0) {
                if ($incremental) {
                    $cost[$type] = $this->formulas->getDevelopmentCost($basePrice, $price->getFactor(), $level);
                } else {
                    $cost[$type] = floor($basePrice);
                }

                if ($destroy) {
                    $cost[$type] = $this->formulas->getTearDownCost(
                        $basePrice,
                        $price->getFactor(),
                        $level,
                        $ionTechLevel
                    );
                }
            }
        }

        return $cost;
    }

    /**
     * @param array<string, float> $planetResources Keys: planet_metal, planet_crystal, planet_deuterium, planet_energy_max
     */
    public function isDevelopmentPayable(array $planetResources, int $elementId, int $currentLevel, bool $incremental = true, bool $destroy = false, int $ionTechLevel = 0): bool
    {
        $costs = $this->developmentPrice($elementId, $currentLevel, $incremental, $destroy, $ionTechLevel);

        foreach ($costs as $resource => $amount) {
            if ($amount > ($planetResources['planet_' . $resource] ?? 0)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate development time based on the element type.
     */
    public function developmentTime(int $elementId, int $currentLevel, int $roboticsFactory, int $naniteFactory, int $totalLabLevel, int $astrophysicsLevel, bool $technocrateActive): int
    {
        $object = $this->registry->get($elementId);
        $price = $object->getPrice();

        $costMetal = $this->formulas->getDevelopmentCost($price->getMetal(), $price->getFactor(), $currentLevel);
        $costCrystal = $this->formulas->getDevelopmentCost($price->getCrystal(), $price->getFactor(), $currentLevel);

        $time = 1;

        if ($object instanceof Building) {
            $time = $this->formulas->getBuildingTime(
                $costMetal,
                $costCrystal,
                $elementId,
                $roboticsFactory,
                $naniteFactory,
                $currentLevel
            );
        }

        if ($object instanceof Research) {
            $time = $this->formulas->getResearchTime($costMetal, $costCrystal, $totalLabLevel, $astrophysicsLevel);
            $time = floor($time * (1 - ($technocrateActive ? TECHNOCRATE_SPEED : 0)));
        }

        if ($object instanceof Ship || $object instanceof Defense) {
            $time = $this->formulas->getShipyardProductionTime(
                $costMetal,
                $costCrystal,
                $elementId,
                $roboticsFactory,
                $naniteFactory
            );
        }

        return (int) ($time < 1 ? 1 : $time);
    }

    public function tearDownTime(int $buildingId, int $level, int $roboticsFactory, int $naniteFactory): float
    {
        $price = $this->registry->get($buildingId)->getPrice();

        $metalCost = $this->formulas->getTearDownBaseCost($price->getMetal(), $price->getFactor(), $level);
        $crystalCost = $this->formulas->getTearDownBaseCost($price->getCrystal(), $price->getFactor(), $level);

        return $this->formulas->getTearDownTime($metalCost, $crystalCost, $buildingId, $roboticsFactory, $naniteFactory, $level);
    }

    /**
     * Check if all requirements for a development are met.
     *
     * @param array<int, int> $levels Object ID => current level (combined user + planet levels)
     */
    public function isDevelopmentAllowed(int $elementId, array $levels): bool
    {
        $object = $this->registry->get($elementId);

        if (!$object instanceof \App\Core\GameObjects\GameObject) {
            return true;
        }

        $requirements = $object->getRequirements();

        if ($requirements->isEmpty()) {
            return true;
        }

        foreach ($requirements as $reqId => $reqLevel) {
            if (($levels[$reqId] ?? 0) < $reqLevel) {
                return false;
            }
        }

        return true;
    }

    public function isLabWorking(int $currentResearch): bool
    {
        return ($currentResearch != 0);
    }

    public function isShipyardWorking(int $hangarQueue): bool
    {
        return ($hangarQueue != 0);
    }

    public function areFieldsAvailable(int $currentFields, int $planetFieldMax, int $terraformerLevel): bool
    {
        return ($currentFields < $this->maxFields($planetFieldMax, $terraformerLevel));
    }
}
