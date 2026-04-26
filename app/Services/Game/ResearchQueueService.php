<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Models\Buildings;
use App\Models\Planets;
use App\Models\ResearchQueue;
use App\Models\User;
use App\Models\UsersStatistics;
use App\Services\Game\Formulas\DevelopmentsService;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\ResearchEnumerator;
use Xgp\App\Libraries\StatisticsLibrary;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class ResearchQueueService
{
    public const MAX_QUEUE_SIZE = 5;

    public function __construct(
        private GameObjectRegistry $registry,
        private DevelopmentsService $developmentsService,
    ) {
    }

    /**
     * @param array<string,mixed> $userData
     */
    public function add(User $user, Planets $planet, array $userData, int $techId, bool $technocrateActive): bool
    {
        $queue = $user->researchQueue()->orderBy('position')->get();
        $count = $queue->count();

        if ($count >= self::MAX_QUEUE_SIZE) {
            return false;
        }

        $levels = $this->buildLevels($planet, $userData);

        if (!$this->developmentsService->isDevelopmentAllowed($techId, $levels)) {
            return false;
        }

        $techCol = $this->registry->get($techId)->getName();
        $alreadyQueued = $queue->where('tech_id', $techId)->count();
        $currentLevel = (int) ($userData[$techCol] ?? 0);
        $levelForCalc = $currentLevel + $alreadyQueued;

        $labLevel = $this->labLevel($user, $planet);
        $astrophysicsCol = $this->registry->get(ResearchEnumerator::research_astrophysics)->getName();
        $astrophysicsLevel = (int) ($userData[$astrophysicsCol] ?? 0);

        $duration = $this->developmentsService->developmentTime(
            $techId,
            $levelForCalc,
            0,
            0,
            $labLevel,
            $astrophysicsLevel,
            $technocrateActive
        );

        $endTime = (int) $queue->last()?->end_time + $duration;

        if ($count === 0) {
            if (!$this->developmentsService->isDevelopmentPayable(
                $this->planetResources($planet),
                $techId,
                $levelForCalc
            )) {
                return false;
            }

            $endTime = time() + $duration;

            $cost = $this->developmentsService->developmentPrice($techId, $levelForCalc);
            $planet->planet_metal -= $cost['metal'] ?? 0;
            $planet->planet_crystal -= $cost['crystal'] ?? 0;
            $planet->planet_deuterium -= $cost['deuterium'] ?? 0;
            $planet->planet_b_tech_id = $techId;
            $planet->planet_b_tech = $endTime;
            $planet->save();

            $user->research->research_current_research = $planet->planet_id;
            $user->research->save();
        }

        ResearchQueue::create([
            'user_id' => $user->id,
            'planet_id' => $planet->planet_id,
            'position' => $count + 1,
            'tech_id' => $techId,
            'target_level' => $levelForCalc + 1,
            'duration' => $duration,
            'end_time' => $endTime,
        ]);

        return true;
    }

    public function cancel(User $user): bool
    {
        $item = $user->researchQueue()->where('position', 1)->first();

        if ($item === null) {
            return false;
        }

        $workingPlanet = Planets::find($item->planet_id);

        if ($workingPlanet !== null) {
            $cost = $this->developmentsService->developmentPrice($item->tech_id, $item->target_level - 1);
            $workingPlanet->planet_metal += $cost['metal'] ?? 0;
            $workingPlanet->planet_crystal += $cost['crystal'] ?? 0;
            $workingPlanet->planet_deuterium += $cost['deuterium'] ?? 0;
            $workingPlanet->planet_b_tech_id = 0;
            $workingPlanet->planet_b_tech = 0;
            $workingPlanet->save();
        }

        $item->delete();
        $this->shiftPositionsDown($user);

        $nextItem = $user->researchQueue()->where('position', 1)->first();

        if ($nextItem !== null) {
            $this->startItem($user, $nextItem);
            return true;
        }

        $user->research->research_current_research = 0;
        $user->research->save();

        return true;
    }

    public function removeAt(User $user, int $position): void
    {
        if ($position <= 1) {
            return;
        }

        $item = $user->researchQueue()->where('position', $position)->first();

        if ($item === null) {
            return;
        }

        $removedDuration = $item->duration;
        $item->delete();

        foreach (
            $user->researchQueue()->where('position', '>', $position)->orderBy('position')->get() as $r
        ) {
            $r->position -= 1;
            $r->end_time -= $removedDuration;
            $r->save();
        }
    }

    public function processCompletions(User $user): void
    {
        while (true) {
            $item = $user->researchQueue()->where('position', 1)->first();

            if ($item === null || $item->end_time > time()) {
                break;
            }

            $techCol = $this->registry->get($item->tech_id)->getName();
            $newLevel = (int) $user->research->$techCol + 1;

            $user->research->$techCol = $newLevel;
            $user->research->research_current_research = 0;
            $user->research->save();

            $points = StatisticsLibrary::calculatePoints((string) $item->tech_id, $newLevel, 'tech');

            UsersStatistics::where('user_statistic_user_id', $user->id)
                ->increment('user_statistic_technology_points', $points);

            $activePlanet = Planets::find($item->planet_id);

            if ($activePlanet !== null) {
                $activePlanet->planet_b_tech_id = 0;
                $activePlanet->planet_b_tech = 0;
                $activePlanet->save();
            }

            $item->delete();
            $this->shiftPositionsDown($user);

            $nextItem = $user->researchQueue()->where('position', 1)->first();

            if ($nextItem !== null) {
                $this->startItem($user, $nextItem);
            }
        }
    }

    public function labLevel(User $user, Planets $planet): int
    {
        $intergalCol = $this->registry->get(ResearchEnumerator::research_intergalactic_research_network)->getName();
        $intergalLevel = (int) $user->research->$intergalCol;

        if ($intergalLevel < 1) {
            $labCol = $this->registry->get(BuildingsEnumerator::BUILDING_LABORATORY)->getName();
            return (int) ($planet->buildings?->$labCol ?? 0);
        }

        return (int) Buildings::selectRaw('SUM(building_laboratory) AS total_level')
            ->join('planets', 'planet_id', '=', 'building_planet_id')
            ->where('planet_user_id', $user->id)
            ->orderBy('building_laboratory', 'DESC')
            ->limit($intergalLevel + 1)
            ->value('total_level');
    }

    private function startItem(User $user, ResearchQueue $item): void
    {
        $planet = Planets::find($item->planet_id);

        if ($planet !== null) {
            $cost = $this->developmentsService->developmentPrice($item->tech_id, $item->target_level - 1);
            $planet->planet_metal -= $cost['metal'] ?? 0;
            $planet->planet_crystal -= $cost['crystal'] ?? 0;
            $planet->planet_deuterium -= $cost['deuterium'] ?? 0;
            $planet->planet_b_tech_id = $item->tech_id;
            $planet->planet_b_tech = $item->end_time;
            $planet->save();
        }

        $user->research->research_current_research = $item->planet_id;
        $user->research->save();
    }

    private function shiftPositionsDown(User $user): void
    {
        foreach ($user->researchQueue()->orderBy('position')->get() as $item) {
            $item->position -= 1;
            $item->save();
        }
    }

    /** @return array<string, float> */
    private function planetResources(Planets $planet): array
    {
        return [
            'planet_metal' => $planet->planet_metal,
            'planet_crystal' => $planet->planet_crystal,
            'planet_deuterium' => $planet->planet_deuterium,
            'planet_energy_max' => (float) $planet->planet_energy_max,
        ];
    }

    /**
     * @param array<string,mixed> $userData
     *
     * @return array<int, int>
     */
    private function buildLevels(Planets $planet, array $userData): array
    {
        $levels = [];
        foreach ($this->registry->all() as $id => $obj) {
            $column = $obj->getName();
            $levels[$id] = (int) ($planet->buildings?->$column ?? $userData[$column] ?? 0);
        }
        return $levels;
    }
}
