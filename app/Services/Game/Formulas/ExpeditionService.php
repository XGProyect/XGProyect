<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

use App\Services\SettingsService;

class ExpeditionService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * Also applies for resources
     */
    public function getMaxExpeditionPoints(int | float $topPlayerPoints): int
    {
        if ($topPlayerPoints < 100000) {
            return 2500;
        }

        if ($topPlayerPoints < 1000000) {
            return 6000;
        }

        if ($topPlayerPoints < 5000000) {
            return 9000;
        }

        if ($topPlayerPoints < 25000000) {
            return 12000;
        }

        if ($topPlayerPoints < 50000000) {
            return 15000;
        }

        if ($topPlayerPoints < 75000000) {
            return 18000;
        }

        if ($topPlayerPoints < 100000000) {
            return 21000;
        }

        return 25000;
    }

    public function getMaxShipsExpeditionPoints(int | float $topPlayerPoints): int
    {
        return $this->getMaxExpeditionPoints($topPlayerPoints) * 100;
    }

    public function calculateExpeditionPoints(int $structuralIntegrity): int
    {
        return ($structuralIntegrity * 5 / 1000);
    }

    public function getExpeditionResult(): string
    {
        return $this->pickWeighted($this->getExpeditionResultWeights(), 'nothing');
    }

    public function calculateDarkMatterSourceSize(): string
    {
        return $this->pickWeighted($this->getDarkMatterSourceSizeWeights(), 'small');
    }

    public function getDarkMatterSourceSize(string $discoveryType): int
    {
        if ($discoveryType === 'medium') {
            return mt_rand(500, 700);
        }

        if ($discoveryType === 'large') {
            return mt_rand(1000, 1800);
        }

        return mt_rand(300, 400); // $discoveryType === 'small'
    }

    public function calculateResourceTypeObtained(): string
    {
        return $this->pickWeighted($this->getResourceTypeWeights(), 'metal');
    }

    public function calculateResourceSourceSize(): string
    {
        return $this->pickWeighted($this->getResourceSourceSizeWeights(), 'normal');
    }

    public function getResourceSourceSizeMultChances(string $discoveryType): int
    {
        if ($discoveryType === 'large') {
            return mt_rand(50, 100);
        }

        if ($discoveryType === 'xl') {
            return mt_rand(100, 200);
        }

        return mt_rand(10, 50); // $discoveryType === 'normal'
    }

    public function getResourceFoundAmount(int $chancesMultiplier, int $expeditionPoints, string $resourceType): int
    {
        $resource = [
            'metal' => 1,
            'crystal' => 2,
            'deuterium' => 3,
        ];

        return (int) floor($chancesMultiplier * $expeditionPoints / $resource[$resourceType]);
    }

    public function calculateShipFoundAmount(int $chancesMultiplier, int $expeditionPoints): int
    {
        return (int) floor($chancesMultiplier * $expeditionPoints / 2);
    }

    /**
     * Only these ships are computed for the expeditions points
     *
     * @return array<Int, Int>
     */
    public function getPossibleShips(): array
    {
        return [
            202, // ship_small_cargo_ship
            203, // ship_big_cargo_ship
            204, // ship_light_fighter
            205, // ship_heavy_fighter
            206, // ship_cruiser
            207, // ship_battleship
            210, // ship_espionage_probe
            211, // ship_bomber
            213, // ship_destroyer
            215, // ship_reaper
        ];
    }

    /**
     * Only these ships are obtainable on an expedition
     *
     * @return array<Int, Float>
     */
    public function getShipsObtainableChances(): array
    {
        return [
            202 => 0.1, // ship_small_cargo_ship
            203 => 0.1, // ship_big_cargo_ship
            204 => 0.1, // ship_light_fighter
            205 => 0.5, // ship_heavy_fighter
            206 => 0.25, // ship_cruiser
            207 => 0.125, // ship_battleship
            210 => 0.1, // ship_espionage_probe
            211 => 0.0625, // ship_bomber
            213 => 0.0625, // ship_destroyer
            215 => 0.0625, // ship_reaper
        ];
    }

    public function getFleetDeplay(): int
    {
        return $this->pickWeighted($this->getFleetDelayWeights(), 2);
    }

    /** @return array<string, int> */
    public function getExpeditionResultWeights(): array
    {
        return [
            'darkMatter' => $this->settings->getInt('expedition_result_dark_matter_weight'),
            'ships' => $this->settings->getInt('expedition_result_ships_weight'),
            'resources' => $this->settings->getInt('expedition_result_resources_weight'),
            'pirates' => $this->settings->getInt('expedition_result_pirates_weight'),
            'aliens' => $this->settings->getInt('expedition_result_aliens_weight'),
            'delay' => $this->settings->getInt('expedition_result_delay_weight'),
            'early' => $this->settings->getInt('expedition_result_early_weight'),
            'nothing' => $this->settings->getInt('expedition_result_nothing_weight'),
            'merchant' => $this->settings->getInt('expedition_result_merchant_weight'),
            'blackHole' => $this->settings->getInt('expedition_result_black_hole_weight'),
        ];
    }

    /** @return array<string, int> */
    public function getDarkMatterSourceSizeWeights(): array
    {
        return [
            'small' => $this->settings->getInt('expedition_dark_matter_source_small_weight'),
            'medium' => $this->settings->getInt('expedition_dark_matter_source_medium_weight'),
            'large' => $this->settings->getInt('expedition_dark_matter_source_large_weight'),
        ];
    }

    /** @return array<string, int> */
    public function getResourceTypeWeights(): array
    {
        return [
            'metal' => $this->settings->getInt('expedition_resource_type_metal_weight'),
            'crystal' => $this->settings->getInt('expedition_resource_type_crystal_weight'),
            'deuterium' => $this->settings->getInt('expedition_resource_type_deuterium_weight'),
        ];
    }

    /** @return array<string, int> */
    public function getResourceSourceSizeWeights(): array
    {
        return [
            'normal' => $this->settings->getInt('expedition_resource_source_normal_weight'),
            'large' => $this->settings->getInt('expedition_resource_source_large_weight'),
            'xl' => $this->settings->getInt('expedition_resource_source_xl_weight'),
        ];
    }

    /** @return array<int, int> */
    public function getFleetDelayWeights(): array
    {
        return [
            2 => $this->settings->getInt('expedition_fleet_delay_2_weight'),
            3 => $this->settings->getInt('expedition_fleet_delay_3_weight'),
            5 => $this->settings->getInt('expedition_fleet_delay_5_weight'),
        ];
    }

    /**
     * @template T of array-key
     *
     * @param array<T, int> $weights
     * @param T $fallback
     *
     * @return T
     */
    private function pickWeighted(array $weights, string | int $fallback): string | int
    {
        $randomNumber = mt_rand(1, array_sum($weights));
        $sum = 0;

        foreach ($weights as $result => $weight) {
            $sum += $weight;

            if ($randomNumber <= $sum) {
                return $result;
            }
        }

        return $fallback;
    }
}
