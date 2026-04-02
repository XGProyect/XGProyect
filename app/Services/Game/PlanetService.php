<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Planets;
use App\Services\SettingsService;

class PlanetService
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    /** @return array{galaxy: int, system: int, planet: int} */
    public function calculateNewPlanetPosition(): array
    {
        return $this->isPlanetFree(
            $this->settingsService->getInt('lastsettedgalaxypos'),
            $this->settingsService->getInt('lastsettedsystempos'),
            max($this->settingsService->getInt('lastsettedplanetpos'), 4) // new users need to start at position 4
        );
    }

    /** @return array{galaxy: int, system: int, planet: int} */
    public function isPlanetFree(int $galaxy, int $system, int $position): array
    {
        // Check if the planet is free
        $isFree = Planets::where(['planet_galaxy' => $galaxy, 'planet_system' => $system, 'planet_planet' => $position])->first();

        if ($isFree === null) {
            $this->settingsService->write('lastsettedgalaxypos', $galaxy);
            $this->settingsService->write('lastsettedsystempos', $system);
            $this->settingsService->write('lastsettedplanetpos', $position);

            return [
                'galaxy' => $galaxy,
                'system' => $system,
                'planet' => $position,
            ];
        }

        // If the planet is not free, try the next position
        if ($position < 12) {
            return $this->isPlanetFree($galaxy, $system, $position + PLANET_SEPARATION_FACTOR);
        }

        // If we've tried all positions in this system, try the next system
        if ($system < MAX_SYSTEM_IN_GALAXY) {
            return $this->isPlanetFree($galaxy, $system + SYSTEM_SEPARATION_FACTOR, 4);
        }

        // If we've tried all systems in this galaxy, try the next galaxy
        if ($galaxy < MAX_GALAXY_IN_WORLD) {
            return $this->isPlanetFree($galaxy + GALAXY_SEPARATION_FACTOR, 1, 4);
        }

        // If we've tried all galaxies and haven't found a free planet, restart the search
        return $this->isPlanetFree(1, 1, 4);
    }
}
