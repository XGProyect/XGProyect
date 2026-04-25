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
use App\Models\Buildings;
use App\Models\Research;
use App\Models\User;
use App\Services\FormatService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class TechnologytreeController extends BaseController
{
    public function __construct(
        private FormatService $formatService,
        private GameObjectRegistry $registry,
        private SettingsService $settings,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Technology));

        /** @var User $user */
        $user = Auth::user();
        $planet = $user->planets()->where('planet_id', $user->current_planet)->with('buildings')->firstOrFail();
        $buildings = $planet->buildings ?? new Buildings();
        $research = $user->research;

        return view('technologytree.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'constructions' => $this->buildList($this->registry->buildings(), $buildings, $research),
            'research' => $this->buildList($this->registry->research(), $buildings, $research),
            'ships' => $this->buildList($this->registry->ships(), $buildings, $research),
            'defenses' => $this->buildList(
                $this->registry->defenses()->filter(fn (Defense $d) => $d->getId() < 500),
                $buildings,
                $research
            ),
            'missiles' => $this->buildList(
                $this->registry->defenses()->filter(fn (Defense $d) => $d->getId() >= 500),
                $buildings,
                $research
            ),
        ]);
    }

    /**
     * @template TObject of GameObjectInterface
     * @param Collection<int, TObject> $objects
     * @return array<int, array{id: int, name: string, detail: string, requirements: string}>
     */
    private function buildList(Collection $objects, Buildings $buildings, Research $research): array
    {
        return $objects->map(fn (GameObjectInterface $object) => [
            'id' => $object->getId(),
            'name' => $this->translatedName($object),
            'detail' => '',
            'requirements' => implode('<br>', $this->buildRequirements($object, $buildings, $research)),
        ])->values()->all();
    }

    /**
     * @return array<int, string>
     */
    private function buildRequirements(GameObjectInterface $object, Buildings $buildings, Research $research): array
    {
        if (!$object instanceof Building && !$object instanceof ResearchObject &&
            !$object instanceof Ship && !$object instanceof Defense) {
            return [];
        }

        return $object->getRequirements()
            ->map(function (int $requiredLevel, int $reqId) use ($buildings, $research): string {
                $req = $this->registry->get($reqId);
                $currentLevel = $this->currentLevel($req, $buildings, $research);

                $label = $this->translatedName($req) . ' (' . __('game/global.level') . $currentLevel . '/' . $requiredLevel . ')';

                return $currentLevel >= $requiredLevel
                    ? $this->formatService->colorGreen($label)
                    : $this->formatService->colorRed($label);
            })
            ->values()
            ->all();
    }

    private function currentLevel(GameObjectInterface $object, Buildings $buildings, Research $research): int
    {
        $name = $object->getName();

        return match (true) {
            $object instanceof Building => (int) ($buildings->{$name} ?? 0),
            $object instanceof ResearchObject => (int) ($research->{$name} ?? 0),
            default => 0,
        };
    }

    private function translatedName(GameObjectInterface $object): string
    {
        return (string) __('game/' . $this->translationGroup($object) . '.' . $object->getName());
    }

    private function translationGroup(GameObjectInterface $object): string
    {
        return match (true) {
            $object instanceof Building => 'constructions',
            $object instanceof ResearchObject => 'technologies',
            $object instanceof Ship => 'ships',
            $object instanceof Defense => 'defenses',
            default => throw new RuntimeException('Unknown object type for: ' . $object->getName()),
        };
    }
}
