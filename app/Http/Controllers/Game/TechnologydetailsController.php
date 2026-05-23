<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObject;
use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Research;
use App\Core\GameObjects\Ship;
use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\TechnologyInfoService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class TechnologydetailsController extends BaseController
{
    public function __construct(
        private GameObjectRegistry $registry,
        private SettingsService $settings,
        private FormatService $formatService,
        private TechnologyInfoService $technologyInfoService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(Request $request): View | RedirectResponse
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Information));

        $legacyTechnology = $request->integer('gid');
        $technology = $request->integer('technology');

        if ($technology === 0 && $legacyTechnology > 0) {
            $technology = $legacyTechnology;
        }

        if (!$this->registry->has($technology)) {
            return redirect('game.php?page=technologytree');
        }

        $object = $this->registry->get($technology);

        if (!$object instanceof GameObject) {
            return redirect('game.php?page=technologytree');
        }

        $userData = Users::getInstance()->getUserData();
        $planetData = Users::getInstance()->getPlanetData();

        if ($legacyTechnology > 0 && $request->query('page') === 'infos' && $request->isMethod('get')) {
            return redirect('game.php?page=technologydetails&technology=' . $technology);
        }

        if ($technology === 43 && $request->isMethod('post')) {
            $jumpGateInput = $this->validateJumpGateInput($request);

            if ($jumpGateInput instanceof RedirectResponse) {
                return $jumpGateInput;
            }

            $jumpGateResult = $this->technologyInfoService->handleJumpGate(
                $technology,
                $jumpGateInput['targetPlanet'],
                $jumpGateInput['ships'],
                $userData,
                $planetData,
            );

            return redirect('game.php?page=technologydetails&technology=' . $technology)
                ->with('technologyinfo_notice_message', $jumpGateResult['message'])
                ->with('technologyinfo_notice_color', $jumpGateResult['color']);
        }

        $levels = $this->buildLevels($planetData, $userData);
        $name = $this->translatedName($object);

        return view('technologydetails.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'id' => $technology,
            'name' => $name,
            'techInfo' => $this->technologyInfoService->buildViewData($technology, $userData, $planetData),
            'requirements' => $this->buildRequirementsHtml($object, $levels),
            'applicationsTitle' => __('game/technologydetails.applications_title', ['technology' => $name]),
            'applications' => $this->buildApplications($object, $levels),
        ]);
    }

    /**
     * @return array{targetPlanet: int, ships: array<int, int>}|RedirectResponse
     */
    private function validateJumpGateInput(Request $request): array | RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'jmpto' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return redirect('game.php?page=technologydetails&technology=43')
                ->withErrors($validator)
                ->withInput();
        }

        $ships = [];

        foreach ($this->registry->ships() as $ship) {
            if ($ship->getId() < 200 || $ship->getId() >= 250) {
                continue;
            }

            $ships[$ship->getId()] = max(0, $request->integer('c' . $ship->getId()));
        }

        return [
            'targetPlanet' => (int) $validator->validated()['jmpto'],
            'ships' => $ships,
        ];
    }

    /**
     * @param array<string, mixed> $planetData
     * @param array<string, mixed> $userData
     *
     * @return array<int, int>
     */
    private function buildLevels(array $planetData, array $userData): array
    {
        $levels = [];

        foreach ($this->registry->all() as $id => $candidate) {
            $levels[$id] = (int) ($planetData[$candidate->getName()] ?? $userData[$candidate->getName()] ?? 0);
        }

        return $levels;
    }

    /**
     * @param array<int, int> $levels
     */
    private function buildRequirementsHtml(GameObject $object, array $levels): string
    {
        $requirements = $this->buildRequirements($object, $levels);

        if ($requirements === []) {
            return __('game/technologydetails.no_requirements');
        }

        return implode('<br>', $requirements);
    }

    /**
     * @param array<int, int> $levels
     *
     * @return array<int, string>
     */
    private function buildRequirements(GameObject $object, array $levels): array
    {
        return $object->getRequirements()
            ->map(function (int $requiredLevel, int $reqId) use ($levels): string {
                $requirement = $this->registry->get($reqId);
                $currentLevel = $levels[$reqId] ?? 0;
                $label = $this->translatedName($requirement) . ' (' . __('game/global.level') . $currentLevel . '/' . $requiredLevel . ')';

                return $currentLevel >= $requiredLevel
                    ? $this->formatService->colorGreen($label)
                    : $this->formatService->colorRed($label);
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int, int> $levels
     *
     * @return array<int, array{id: int, name: string, requirements: string}>
     */
    private function buildApplications(GameObject $object, array $levels): array
    {
        return $this->registry->all()
            ->filter(
                fn ($candidate) => $candidate instanceof GameObject &&
                    $candidate->getId() !== $object->getId() &&
                    $candidate->getRequirements()->has($object->getId())
            )
            ->map(fn (GameObject $candidate) => [
                'id' => $candidate->getId(),
                'name' => $this->translatedName($candidate),
                'requirements' => $this->buildRequirementsHtml($candidate, $levels),
            ])
            ->values()
            ->all();
    }

    private function translatedName(GameObjectInterface $object): string
    {
        return (string) __('game/' . $this->resolveObjectType($object) . '.' . $object->getName());
    }

    private function resolveObjectType(GameObjectInterface $object): string
    {
        return match (true) {
            $object instanceof Building => 'constructions',
            $object instanceof Research => 'technologies',
            $object instanceof Ship => 'ships',
            $object instanceof Defense => 'defenses',
            default => throw new RuntimeException('Unknown object type for: ' . $object->getName()),
        };
    }
}
