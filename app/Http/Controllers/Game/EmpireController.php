<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Research as ResearchObject;
use App\Core\GameObjects\Ship;
use App\Enums\Module;
use App\Models\BuildingQueue;
use App\Models\Planets;
use App\Models\Research;
use App\Models\ResearchQueue;
use App\Models\User;
use App\Services\FormatService;
use App\Services\Game\BuildingQueueService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\ResearchQueueService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class EmpireController extends BaseController
{
    public function __construct(
        private FormatService $formatService,
        private DevelopmentsService $developmentsService,
        private GameObjectRegistry $registry,
        private SettingsService $settingsService,
        private BuildingQueueService $buildingQueueService,
        private ResearchQueueService $researchQueueService,
    ) {
    }

    public function __invoke(): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Empire));

        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('research');

        $this->processQueueCompletions($user, $this->loadPlanets($user));
        $user->refresh();
        $user->loadMissing('research');

        $research = $user->research ?? new Research();
        $researchQueue = $user->researchQueue()->orderBy('position')->get();
        $planets = $this->loadPlanets($user);

        return view(
            'empire.view',
            array_merge(
                [
                    'gameTitle' => $this->settingsService->getString('game_name'),
                    'planetsAmount' => $planets->count() + 1,
                ],
                $this->buildViewData($planets, $research, $researchQueue)
            )
        );
    }

    /** @return EloquentCollection<int, Planets> */
    private function loadPlanets(User $user): EloquentCollection
    {
        return $user->planets()
            ->with(['buildings', 'defenses', 'ships', 'buildingQueue'])
            ->where('planet_destroyed', 0)
            ->get();
    }

    /** @param EloquentCollection<int, Planets> $planets */
    private function processQueueCompletions(User $user, EloquentCollection $planets): void
    {
        $userData = $this->userData($user);

        $planets->each(function (Planets $planet) use ($userData): void {
            $this->buildingQueueService->processCompletions($planet, $userData);
        });

        $this->researchQueueService->processCompletions($user);
    }

    /** @return array<string, mixed> */
    private function userData(User $user): array
    {
        $research = $user->research ?? new Research();

        return array_merge($user->getAttributes(), $research->getAttributes());
    }

    /**
     * @param  EloquentCollection<int, Planets>  $planets
     * @param  EloquentCollection<int, ResearchQueue>  $researchQueue
     *
     * @return array<string, mixed>
     */
    private function buildViewData(EloquentCollection $planets, Research $research, EloquentCollection $researchQueue): array
    {
        return [
            'image' => $planets->map(fn (Planets $planet): array => $this->imageCell($planet))->all(),
            'name' => $planets->map(fn (Planets $planet): array => $this->nameCell($planet))->all(),
            'coords' => $planets->map(fn (Planets $planet): array => $this->coordsCell($planet))->all(),
            'fields' => $planets->map(fn (Planets $planet): array => $this->fieldsCell($planet))->all(),
            'metalRow' => $this->resourceRows($planets, 'metal'),
            'crystalRow' => $this->resourceRows($planets, 'crystal'),
            'deuteriumRow' => $this->resourceRows($planets, 'deuterium'),
            'energyRow' => $this->energyRows($planets),
            'resources' => $this->objectRows($planets, $this->registry->resourceBuildings(), 'constructions', $research, $researchQueue),
            'facilities' => $this->objectRows($planets, $this->facilityAndMoonBuildings(), 'constructions', $research, $researchQueue),
            'defenses' => $this->objectRows(
                $planets,
                $this->registry->defenses()->filter(fn (Defense $defense): bool => $defense->getId() < 500),
                'defenses',
                $research,
                $researchQueue
            ),
            'missiles' => $this->objectRows(
                $planets,
                $this->registry->defenses()->filter(fn (Defense $defense): bool => $defense->getId() >= 500),
                'defenses',
                $research,
                $researchQueue
            ),
            'tech' => $this->objectRows($planets, $this->registry->research(), 'technologies', $research, $researchQueue),
            'fleet' => $this->objectRows($planets, $this->registry->ships(), 'ships', $research, $researchQueue),
        ];
    }

    /** @return array{planetId: int, planetImage: string, planetName: string} */
    private function imageCell(Planets $planet): array
    {
        return [
            'planetId' => $planet->planet_id,
            'planetImage' => $planet->planet_image,
            'planetName' => $planet->planet_name,
        ];
    }

    /** @return array{planetName: string} */
    private function nameCell(Planets $planet): array
    {
        return [
            'planetName' => $planet->planet_name,
        ];
    }

    /** @return array{planetCoords: string, planetGalaxy: int, planetSystem: int} */
    private function coordsCell(Planets $planet): array
    {
        return [
            'planetCoords' => $this->formatService->formatCoords($planet->planet_galaxy, $planet->planet_system, $planet->planet_planet),
            'planetGalaxy' => $planet->planet_galaxy,
            'planetSystem' => $planet->planet_system,
        ];
    }

    /** @return array{planetFieldCurrent: int, planetFieldMax: int} */
    private function fieldsCell(Planets $planet): array
    {
        return [
            'planetFieldCurrent' => $planet->planet_field_current,
            'planetFieldMax' => $planet->planet_field_max,
        ];
    }

    /**
     * @param  EloquentCollection<int, Planets>  $planets
     *
     * @return array<int, array{planetId: int, planetType: int, planetCurrentAmount: string, planetProduction: string}>
     */
    private function resourceRows(EloquentCollection $planets, string $resource): array
    {
        return $planets->map(function (Planets $planet) use ($resource): array {
            $amount = $this->planetResourceAmount($planet, $resource);
            $production = $this->planetResourceProduction($planet, $resource)
                + $this->settingsService->getInt($resource . '_basic_income');

            return [
                'planetId' => $planet->planet_id,
                'planetType' => $planet->planet_type,
                'planetCurrentAmount' => $this->formatService->prettyNumber($amount),
                'planetProduction' => $this->formatService->prettyNumber($production),
            ];
        })->all();
    }

    private function planetResourceAmount(Planets $planet, string $resource): float
    {
        return match ($resource) {
            'metal' => $planet->planet_metal,
            'crystal' => $planet->planet_crystal,
            'deuterium' => $planet->planet_deuterium,
            default => throw new RuntimeException('Unknown empire resource: ' . $resource),
        };
    }

    private function planetResourceProduction(Planets $planet, string $resource): int
    {
        return match ($resource) {
            'metal' => $planet->planet_metal_perhour,
            'crystal' => $planet->planet_crystal_perhour,
            'deuterium' => $planet->planet_deuterium_perhour,
            default => throw new RuntimeException('Unknown empire resource: ' . $resource),
        };
    }

    /**
     * @param  EloquentCollection<int, Planets>  $planets
     *
     * @return array<int, array{usedEnergy: string, maxEnergy: string}>
     */
    private function energyRows(EloquentCollection $planets): array
    {
        return $planets->map(fn (Planets $planet): array => [
            'usedEnergy' => $this->formatService->prettyNumber($planet->planet_energy_max + $planet->planet_energy_used),
            'maxEnergy' => $this->formatService->prettyNumber($planet->planet_energy_max),
        ])->all();
    }

    /** @return Collection<int, GameObjectInterface> */
    private function facilityAndMoonBuildings(): Collection
    {
        return $this->registry->facilityBuildings()
            ->keys()
            ->merge($this->registry->moonBuildings()->keys())
            ->mapWithKeys(fn (int $id): array => [$id => $this->registry->get($id)]);
    }

    /**
     * @template TObject of GameObjectInterface
     *
     * @param  EloquentCollection<int, Planets>  $planets
     * @param  Collection<int, TObject>  $objects
     * @param  EloquentCollection<int, ResearchQueue>  $researchQueue
     *
     * @return array<int, array{label: string, cells: array<int, array{url: string, value: string}>}>
     */
    private function objectRows(
        EloquentCollection $planets,
        Collection $objects,
        string $translationGroup,
        Research $research,
        EloquentCollection $researchQueue,
    ): array {
        return $objects->map(fn (GameObjectInterface $object): array => [
            'label' => (string) __('game/' . $translationGroup . '.' . $object->getName()),
            'cells' => $planets->map(
                fn (Planets $planet): array => $this->objectCell($planet, $object, $research, $researchQueue)
            )->all(),
        ])->values()->all();
    }

    /**
     * @param  EloquentCollection<int, ResearchQueue>  $researchQueue
     *
     * @return array{url: string, value: string}
     */
    private function objectCell(
        Planets $planet,
        GameObjectInterface $object,
        Research $research,
        EloquentCollection $researchQueue,
    ): array {
        $value = (string) $this->currentLevel($planet, $object, $research);
        $queuedLevel = $this->queuedLevel($planet, $object, $researchQueue);

        if ($queuedLevel !== null) {
            $value .= ' ' . $queuedLevel;
        }

        return [
            'url' => $this->objectUrl($planet, $object),
            'value' => $value,
        ];
    }

    private function currentLevel(Planets $planet, GameObjectInterface $object, Research $research): int
    {
        $name = $object->getName();

        return match (true) {
            $object instanceof Building => (int) ($planet->buildings->{$name} ?? 0),
            $object instanceof ResearchObject => (int) ($research->{$name} ?? 0),
            $object instanceof Ship => (int) ($planet->ships->{$name} ?? 0),
            $object instanceof Defense => (int) ($planet->defenses->{$name} ?? 0),
            default => throw new RuntimeException('Unknown object type for: ' . $name),
        };
    }

    /** @param EloquentCollection<int, ResearchQueue> $researchQueue */
    private function queuedLevel(Planets $planet, GameObjectInterface $object, EloquentCollection $researchQueue): ?string
    {
        if ($object instanceof Building) {
            $item = $planet->buildingQueue->firstWhere('building_id', $object->getId());

            if (!$item instanceof BuildingQueue) {
                return null;
            }

            $level = '(' . $item->target_level . ')';

            return $item->mode === 'destroy'
                ? $this->formatService->colorRed($level)
                : $this->formatService->colorGreen($level);
        }

        if ($object instanceof ResearchObject) {
            $item = $researchQueue
                ->where('planet_id', $planet->planet_id)
                ->firstWhere('tech_id', $object->getId());

            return $item instanceof ResearchQueue
                ? $this->formatService->colorGreen('(' . $item->target_level . ')')
                : null;
        }

        return null;
    }

    private function objectUrl(Planets $planet, GameObjectInterface $object): string
    {
        $page = match (true) {
            $object instanceof Building => $this->developmentsService->setBuildingPage($object->getId()),
            $object instanceof ResearchObject => 'research',
            $object instanceof Ship => 'shipyard',
            $object instanceof Defense => 'defense',
            default => throw new RuntimeException('Unknown object type for: ' . $object->getName()),
        };

        return 'game.php?page=' . $page . '&cp=' . $planet->planet_id . '&re=0&planettype=' . $planet->planet_type;
    }
}
