<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Models\Planets;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;

class DevelopmentDataService
{
    public function __construct(
        private GameObjectRegistry $registry,
        private DevelopmentsService $developmentsService,
        private FormatService $formatService,
    ) {}

    /**
     * @param  array<string,mixed>  $planetData
     * @param  array<string,mixed>  $userData
     * @return array<int, int>
     */
    public function levelsFromData(array $planetData, array $userData): array
    {
        return $this->buildLevels(
            fn (string $column): int => (int) ($planetData[$column] ?? $userData[$column] ?? 0)
        );
    }

    /**
     * @param  array<string,mixed>  $userData
     * @return array<int, int>
     */
    public function levelsFromPlanet(Planets $planet, array $userData): array
    {
        return $this->buildLevels(
            fn (string $column): int => (int) ($planet->buildings?->$column ?? $userData[$column] ?? 0)
        );
    }

    /**
     * @param  Planets|array<string,mixed>  $planet
     * @return array<string, float>
     */
    public function planetResources(Planets|array $planet): array
    {
        if (is_array($planet)) {
            return [
                'planet_metal' => (float) ($planet['planet_metal'] ?? 0),
                'planet_crystal' => (float) ($planet['planet_crystal'] ?? 0),
                'planet_deuterium' => (float) ($planet['planet_deuterium'] ?? 0),
                'planet_energy_max' => (float) ($planet['planet_energy_max'] ?? 0),
            ];
        }

        return [
            'planet_metal' => (float) $planet->planet_metal,
            'planet_crystal' => (float) $planet->planet_crystal,
            'planet_deuterium' => (float) $planet->planet_deuterium,
            'planet_energy_max' => (float) $planet->planet_energy_max,
        ];
    }

    /**
     * @param  Planets|array<string,mixed>  $planet
     */
    public function buildPriceHtml(Planets|array $planet, int $elementId, int $level): string
    {
        $costs = $this->developmentsService->developmentPrice($elementId, $level);
        $resources = $this->planetResources($planet);
        $labels = [
            'metal' => __('game/global.metal'),
            'crystal' => __('game/global.crystal'),
            'deuterium' => __('game/global.deuterium'),
            'energy_max' => __('game/global.energy'),
        ];
        $text = __('game/buildings.require');

        foreach ($labels as $type => $label) {
            if (! isset($costs[$type])) {
                continue;
            }

            $cost = $costs[$type];
            $available = $resources['planet_'.$type] ?? 0;
            $formatted = $this->formatService->prettyNumber((int) $cost);
            $text .= $label.': ';

            if ($cost > $available) {
                $shortage = $this->formatService->prettyNumber($cost - $available);
                $text .= '<b style="color:red;"><t title="-'.$shortage.'"><span class="noresources">'.$formatted.'</span></t></b> ';

                continue;
            }

            $text .= '<b style="color:lime;">'.$formatted.'</b> ';
        }

        return $text;
    }

    /**
     * @param  callable(string):int  $levelResolver
     * @return array<int, int>
     */
    private function buildLevels(callable $levelResolver): array
    {
        $levels = [];

        foreach ($this->registry->all() as $id => $obj) {
            $levels[$id] = $levelResolver($obj->getName());
        }

        return $levels;
    }
}
