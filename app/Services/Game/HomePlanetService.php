<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Planets;
use App\Models\User;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

class HomePlanetService
{
    public function resetCurrentToHome(User $user): ?int
    {
        $homePlanetId = $this->resolveOriginalHomePlanet($user)
            ?? $this->resolveActivePlanet((int) $user->id, (int) $user->home_planet_id)
            ?? $this->resolveFirstActivePlanet((int) $user->id);

        if ($homePlanetId === null) {
            return null;
        }

        $updates = ['current_planet' => $homePlanetId];

        $updates = array_merge($updates, $this->homePlanetUpdates($homePlanetId));

        User::whereKey($user->id)->update($updates);
        $user->forceFill($updates);

        return $homePlanetId;
    }

    public function moveCurrentAfterAbandoningPlanet(int $userId, int $abandonedPlanetId, int $homePlanetId): ?int
    {
        if ($abandonedPlanetId === $homePlanetId) {
            $nextHomePlanetId = $this->resolveFirstActivePlanet($userId, $abandonedPlanetId);

            if ($nextHomePlanetId === null) {
                return null;
            }

            User::whereKey($userId)->update($this->homePlanetUpdates($nextHomePlanetId));

            return $nextHomePlanetId;
        }

        $currentPlanetId = $this->resolveActivePlanet($userId, $homePlanetId)
            ?? $this->resolveFirstActivePlanet($userId, $abandonedPlanetId);

        if ($currentPlanetId === null) {
            return null;
        }

        $updates = ['current_planet' => $currentPlanetId];

        if ($currentPlanetId !== $homePlanetId) {
            $updates = array_merge($updates, $this->homePlanetUpdates($currentPlanetId));
        }

        User::whereKey($userId)->update($updates);

        return $currentPlanetId;
    }

    private function resolveActivePlanet(int $userId, int $planetId): ?int
    {
        if ($planetId <= 0) {
            return null;
        }

        $value = Planets::query()
            ->where('planet_id', $planetId)
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->where('planet_destroyed', 0)
            ->value('planet_id');

        return is_numeric($value) ? (int) $value : null;
    }

    private function resolveOriginalHomePlanet(User $user): ?int
    {
        if ((int) $user->galaxy <= 0 || (int) $user->system <= 0 || (int) $user->planet <= 0) {
            return null;
        }

        $value = Planets::query()
            ->where('planet_user_id', (int) $user->id)
            ->where('planet_galaxy', (int) $user->galaxy)
            ->where('planet_system', (int) $user->system)
            ->where('planet_planet', (int) $user->planet)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->where('planet_destroyed', 0)
            ->value('planet_id');

        return is_numeric($value) ? (int) $value : null;
    }

    /** @return array{home_planet_id: int, current_planet: int, galaxy?: int, system?: int, planet?: int} */
    private function homePlanetUpdates(int $planetId): array
    {
        $planet = Planets::query()
            ->select(['planet_id', 'planet_galaxy', 'planet_system', 'planet_planet'])
            ->where('planet_id', $planetId)
            ->first();

        $updates = [
            'home_planet_id' => $planetId,
            'current_planet' => $planetId,
        ];

        if ($planet !== null) {
            $updates['galaxy'] = (int) $planet->planet_galaxy;
            $updates['system'] = (int) $planet->planet_system;
            $updates['planet'] = (int) $planet->planet_planet;
        }

        return $updates;
    }

    private function resolveFirstActivePlanet(int $userId, ?int $excludePlanetId = null): ?int
    {
        $query = Planets::query()
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->where('planet_destroyed', 0);

        if ($excludePlanetId !== null) {
            $query->where('planet_id', '!=', $excludePlanetId);
        }

        $value = $query
            ->orderBy('planet_galaxy')
            ->orderBy('planet_system')
            ->orderBy('planet_planet')
            ->orderBy('planet_id')
            ->value('planet_id');

        return is_numeric($value) ? (int) $value : null;
    }
}
