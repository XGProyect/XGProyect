<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Ship;
use App\Enums\Game\DriveType;

class FleetsService
{
    public function __construct(
        private GameObjectRegistry $registry,
    ) {
    }

    public function targetDistance(int $origGalaxy, int $destGalaxy, int $origSystem, int $destSystem, int $origPlanet, int $destPlanet): int
    {
        if (($origGalaxy - $destGalaxy) != 0) {
            return abs($origGalaxy - $destGalaxy) * 20000;
        }

        if (($origSystem - $destSystem) != 0) {
            return abs($origSystem - $destSystem) * 5 * 19 + 2700;
        }

        if (($origPlanet - $destPlanet) != 0) {
            return abs($origPlanet - $destPlanet) * 5 + 1000;
        }

        return 5;
    }

    public function missionDuration(int $percentage, int $maxFleetSpeed, int $distance, int $speedFactor): float
    {
        return (35000 / $percentage * sqrt($distance * 10 / $maxFleetSpeed) + 10) / $speedFactor;
    }

    /**
     * Calculate the effective speed of each ship in a fleet, factoring in drive upgrades.
     *
     * @param array<int, int> $fleet        Ship ID => count
     * @param int             $combustion   Combustion drive level
     * @param int             $impulse      Impulse drive level
     * @param int             $hyperspace   Hyperspace drive level
     *
     * @return array<int, float> Ship ID => effective speed
     */
    public function fleetMaxSpeed(array $fleet, int $combustion, int $impulse, int $hyperspace): array
    {
        $speeds = [];

        foreach ($fleet as $shipId => $count) {
            $speeds[$shipId] = $this->getShipSpeed($shipId, $combustion, $impulse, $hyperspace);
        }

        return $speeds;
    }

    public function getShipSpeed(int $shipId, int $combustion, int $impulse, int $hyperspace): float
    {
        if ($shipId === 0) {
            return 0.0;
        }

        /** @var Ship $ship */
        $ship = $this->registry->get($shipId);
        $drive = $ship->getDrive();

        $secondaryTech = $drive->getSecondary() !== null
            ? $this->techLevelFor($drive->getSecondary(), $combustion, $impulse, $hyperspace)
            : 0;
        $tertiaryTech = $drive->getTertiary() !== null
            ? $this->techLevelFor($drive->getTertiary(), $combustion, $impulse, $hyperspace)
            : 0;

        $activeDrive = $drive->getActiveDrive($secondaryTech, $tertiaryTech);
        $baseSpeed = $drive->usesUpgradedSpeed($secondaryTech, $tertiaryTech)
            ? $ship->getSpeed2()
            : $ship->getSpeed();

        $techLevel = $this->techLevelFor($activeDrive, $combustion, $impulse, $hyperspace);

        return $baseSpeed + ($baseSpeed * $techLevel * $activeDrive->speedBonus());
    }

    public function shipConsumption(int $shipId, int $combustion, int $impulse, int $hyperspace): int
    {
        /** @var Ship $ship */
        $ship = $this->registry->get($shipId);
        $drive = $ship->getDrive();

        $secondaryTech = $drive->getSecondary() !== null
            ? $this->techLevelFor($drive->getSecondary(), $combustion, $impulse, $hyperspace)
            : 0;
        $tertiaryTech = $drive->getTertiary() !== null
            ? $this->techLevelFor($drive->getTertiary(), $combustion, $impulse, $hyperspace)
            : 0;

        if ($drive->usesUpgradedSpeed($secondaryTech, $tertiaryTech)) {
            return $ship->getConsumption2();
        }

        return $ship->getConsumption();
    }

    private function techLevelFor(DriveType $drive, int $combustion, int $impulse, int $hyperspace): int
    {
        return match ($drive) {
            DriveType::Combustion => $combustion,
            DriveType::Impulse => $impulse,
            DriveType::Hyperspace => $hyperspace,
            DriveType::None => 0,
        };
    }

    /**
     * @param array<int, int> $fleetArray Ship ID => count
     */
    public function fleetConsumption(array $fleetArray, int $speedFactor, int $missionDuration, int $missionDistance, int $combustion, int $impulse, int $hyperspace): float
    {
        $consumption = 0;

        foreach ($fleetArray as $shipId => $count) {
            if ($shipId > 0) {
                $shipSpeed = $this->getShipSpeed($shipId, $combustion, $impulse, $hyperspace);
                $shipCons = $this->shipConsumption($shipId, $combustion, $impulse, $hyperspace);
                $spd = 35000 / ($missionDuration * $speedFactor - 10) * sqrt($missionDistance * 10 / $shipSpeed);

                $basicConsumption = $spd + $count * $shipCons * pow((($spd / 10) + 1), 2);
                $consumption += $basicConsumption * $missionDistance / 35000 + 1;
            }
        }

        return round($consumption);
    }

    public function getMaxFleets(int $computerTech, bool $admiralActive): int
    {
        return 1 + $computerTech + ($admiralActive ? AMIRAL : 0);
    }

    public function getMaxExpeditions(int $astrophysicsTech): int
    {
        return (int) floor(sqrt($astrophysicsTech));
    }

    public function getMaxColonies(int $astrophysicsTech): int
    {
        return (int) ceil($astrophysicsTech / 2);
    }

    public function getMaxStorage(int $shipStorage, int $hyperspaceTechLevel): int
    {
        return intval($shipStorage + ($shipStorage * 0.05 * $hyperspaceTechLevel));
    }

    public function isFleetReturning(string|int $fleetMess): bool
    {
        return ($fleetMess == 1);
    }
}
