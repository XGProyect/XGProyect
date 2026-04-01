<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

use App\Services\SettingsService;

class FormulasService
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    public function phalanxRange(int $phalanxLevel): int
    {
        if ($phalanxLevel > 1) {
            return (int) pow($phalanxLevel, 2) - 1;
        }

        if ($phalanxLevel == 1) {
            return 1;
        }

        return 0;
    }

    public function missileRange(int $impulseDriveLevel): int
    {
        if ($impulseDriveLevel > 0) {
            return ($impulseDriveLevel * 5) - 1;
        }

        return 0;
    }

    public function getPlanetSize(int $position, bool $main = false): array
    {
        $min = [
            9747, 9849, 9899, 11091, 12166,
            12166, 11874, 12921, 12689, 12410,
            12083, 11662, 10392, 9000, 8062,
        ];

        $max = [
            10392, 10488, 11747, 14491, 14900,
            15748, 15588, 15905, 15588, 15000,
            14318, 13416, 11000, 9644, 8602,
        ];

        $diameter = mt_rand($min[$position - 1], $max[$position - 1]);
        $diameter *= PLANETSIZE_MULTIPLER;

        $fields = $this->calculatePlanetFields($diameter);

        if ($main) {
            $diameter = 12800;
            $fields = $this->settingsService->getInt('initial_fields');
        }

        return [
            'planet_diameter' => $diameter,
            'planet_field_max' => $fields,
        ];
    }

    public function calculatePlanetFields(int $diameter): int
    {
        return (int) pow(($diameter / 1000), 2);
    }

    public function setPlanetImage(int $system, int $position): string
    {
        $planetsAvailable = [
            'dschjungel' => 10,
            'eis' => 10,
            'gas' => 8,
            'normaltemp' => 7,
            'trocken' => 10,
            'wasser' => 9,
            'wuesten' => 4,
        ];

        $type = match (true) {
            $position >= 1 && $position <= 3 => ['trocken', 'wuesten'],
            $position >= 4 && $position <= 5 => ['normaltemp', 'trocken'],
            $position >= 6 && $position <= 7 => ['dschjungel', 'normaltemp'],
            $position >= 8 && $position <= 9 => ['wasser', 'dschjungel'],
            $position >= 10 && $position <= 11 => ['eis', 'wasser'],
            $position >= 12 && $position <= 13 => ['gas', 'eis'],
            $position >= 14 && $position <= 15 => ['normaltemp', 'gas'],
            default => ['normaltemp', 'trocken'],
        };

        $even = ($system % 2 == 0) ? 1 : 0;
        $imageId = mt_rand(1, $planetsAvailable[$type[$even]]);

        return $type[$even] . 'planet' . str_pad((string) $imageId, 2, '0', STR_PAD_LEFT);
    }

    public function setPlanetTemp(int $position): array
    {
        $tempAvailable = [
            1 => [220, 260], 2 => [170, 210], 3 => [120, 160],
            4 => [70, 110], 5 => [60, 100], 6 => [50, 90],
            7 => [40, 80], 8 => [30, 70], 9 => [20, 60],
            10 => [10, 50], 11 => [0, 40], 12 => [-10, 30],
            13 => [-50, -10], 14 => [-90, -50], 15 => [-130, -90],
        ];

        $temperature = mt_rand($tempAvailable[$position][0], $tempAvailable[$position][1]);

        return [
            'min' => $temperature - 40,
            'max' => $temperature,
        ];
    }

    public function getMoonDestructionChance(int $planetDiameter, int $deathStars): int
    {
        $prob = (100 - sqrt($planetDiameter)) * sqrt($deathStars);

        return ($prob > 100) ? 100 : (int) round($prob);
    }

    public function getDeathStarsDestructionChance(int $planetDiameter): int
    {
        return (int) round(sqrt($planetDiameter) / 2);
    }

    public function getIonTechnologyBonus(int $ionTechnologyLevel): float
    {
        return $ionTechnologyLevel * 0.04;
    }

    public function getPlasmaTechnologyBonus(int $plasmaTechnologyLevel, string $resource): float
    {
        $bonus = [
            'metal' => 0.01,
            'crystal' => 0.0066,
            'deuterium' => 0.0033,
        ];

        return $plasmaTechnologyLevel * $bonus[$resource];
    }

    public function getDevelopmentCost(int $price, float $factor, int $level): float
    {
        return round($price * pow($factor, $level));
    }

    public function getTearDownBaseCost(int $price, float $factor, int $level): int
    {
        return (int) floor($this->getDevelopmentCost($price, $factor, ($level - 2)));
    }

    public function getTearDownCost(int $price, float $factor, int $level, int $ionTechnologyLevel): int
    {
        return max(
            (int) floor($this->getTearDownBaseCost($price, $factor, $level) * (1 - $this->getIonTechnologyBonus($ionTechnologyLevel))),
            0
        );
    }

    public function getBuildingTime(float $metalCost, float $crystalCost, int $building, int $roboticsFactory, int $naniteFactory, int $level): float
    {
        return $this->getDevelopmentTime($metalCost, $crystalCost, $building, $roboticsFactory, $naniteFactory, $level);
    }

    public function getShipyardProductionTime(float $metalCost, float $crystalCost, int $shipDefense, int $shipyardLevel, int $naniteFactoryLevel): float
    {
        return $this->getDevelopmentTime($metalCost, $crystalCost, $shipDefense, $shipyardLevel, $naniteFactoryLevel, 0, false);
    }

    public function getResearchTime(float $metalCost, float $crystalCost, int $totalLabLevel, int $expeditionLevel): float
    {
        $universeSpeed = $this->settingsService->getInt('game_speed') / 2500;

        return ($metalCost + $crystalCost) / ($universeSpeed * 1000 * (1 + $totalLabLevel) * (1 + $expeditionLevel)) * 3600;
    }

    public function getTearDownTime(int $metalCost, int $crystalCost, int $building, int $roboticsFactory, int $naniteFactory, int $level): float
    {
        $tearDownTime = $this->getDevelopmentTime($metalCost, $crystalCost, $building, $roboticsFactory, $naniteFactory, $level - 2);

        return ($tearDownTime < 1 ? 1 : $tearDownTime);
    }

    private function getDevelopmentTime(float $metalCost, float $crystalCost, int $object, int $firstBoost, int $secondBoost, int $level = 0, bool $reduce = true): float
    {
        $resourcesNeeded = $metalCost + $crystalCost;
        $reduction = max(4 - ($level + 1) / 2, 1);
        $robotics = 1 + $firstBoost;
        $nanite = pow(2, $secondBoost);
        $universeSpeed = $this->settingsService->getInt('game_speed') / 2500;

        $withoutReduction = [
            15, // Nanite Factory
            41, // Lunar Base
            42, // Sensor Phalanx
            43, // Jump Gate
        ];

        if (in_array($object, $withoutReduction) || !$reduce) {
            $reduction = 1;
        }

        return $resourcesNeeded / (2500 * $reduction * $robotics * $nanite * $universeSpeed) * 3600;
    }
}
