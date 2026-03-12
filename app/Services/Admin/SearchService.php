<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Alliance;
use App\Models\Planets;
use App\Models\User;
use Illuminate\Support\Collection;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator as PlanetTypes;

class SearchService
{
    private const MAX_RESULTS_PER_GROUP = 10;

    /**
     * Returns a flat collection of results, each with: label, type, detail, url.
     *
     * @return Collection<int, array{label: string, type: string, detail: string, url: string}>
     */
    public function search(string $query): Collection
    {
        return collect()
            ->merge($this->searchUsers($query))
            ->merge($this->searchAlliances($query))
            ->merge($this->searchPlanets($query, PlanetTypes::PLANET))
            ->merge($this->searchPlanets($query, PlanetTypes::MOON))
            ->values();
    }

    private function searchUsers(string $query): Collection
    {
        return User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(self::MAX_RESULTS_PER_GROUP)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'label' => $user->name,
                'type' => 'User',
                'detail' => $user->email,
                'url' => route('admin.users.info', $user->id),
            ]);
    }

    private function searchAlliances(string $query): Collection
    {
        return Alliance::query()
            ->where('alliance_name', 'like', "%{$query}%")
            ->orWhere('alliance_tag', 'like', "%{$query}%")
            ->orderBy('alliance_name')
            ->limit(self::MAX_RESULTS_PER_GROUP)
            ->get(['alliance_id', 'alliance_name', 'alliance_tag'])
            ->map(fn (Alliance $alliance) => [
                'label' => $alliance->alliance_name,
                'type' => 'Alliance',
                'detail' => '[' . $alliance->alliance_tag . ']',
                'url' => route('admin.alliances.info', $alliance->alliance_id),
            ]);
    }

    private function searchPlanets(string $query, int $type): Collection
    {
        return Planets::query()
            ->with('user:id,name')
            ->whereHas('user')
            ->where('planet_type', $type)
            ->where('planet_name', 'like', "%{$query}%")
            ->orderBy('planet_name')
            ->limit(self::MAX_RESULTS_PER_GROUP)
            ->get(['planet_id', 'planet_name', 'planet_galaxy', 'planet_system', 'planet_planet', 'planet_user_id'])
            ->map(fn (Planets $planet) => [
                'label' => $planet->planet_name,
                'type' => $type === PlanetTypes::MOON ? 'Moon' : 'Planet',
                'detail' => $planet->planet_galaxy . ':' . $planet->planet_system . ':' . $planet->planet_planet
                    . ' (' . $planet->user->name . ')',
                'url' => $type === PlanetTypes::MOON
                    ? route('admin.users.moons', $planet->planet_user_id)
                    : route('admin.users.planet.edit', [$planet->planet_user_id, $planet->planet_id]),
            ]);
    }
}
