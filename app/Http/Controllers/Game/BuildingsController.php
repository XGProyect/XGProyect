<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Enums\Module;
use App\Models\Planets;
use App\Services\FormatService;
use App\Services\Game\BuildingQueueService;
use App\Services\Game\DevelopmentDataService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
abstract class BuildingsController extends BaseController
{
    protected string $page = '';

    private const WORKING_BUILDINGS = [
        BuildingsEnumerator::BUILDING_ROBOT_FACTORY,
        BuildingsEnumerator::BUILDING_NANO_FACTORY,
        BuildingsEnumerator::BUILDING_HANGAR,
    ];

    public function __construct(
        protected GameObjectRegistry $registry,
        protected DevelopmentsService $developmentsService,
        protected FormatService $formatService,
        protected DevelopmentDataService $developmentDataService,
        protected OfficerService $officerService,
        protected SettingsService $settingsService,
        protected TimingService $timingService,
        protected BuildingQueueService $queueService,
    ) {
    }

    /** @return int[] */
    abstract protected function setAllowedBuildings(int $planetType): array;

    public function __invoke(Request $request): View | RedirectResponse
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Buildings));

        $user = Users::getInstance()->getUserData();
        $planet = Users::getInstance()->getPlanetData();

        if ($request->query('cmd') !== null) {
            return $this->handleAction($request, $user, $planet);
        }

        return $this->showPage($user, $planet);
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     */
    private function handleAction(Request $request, array $user, array $planet): RedirectResponse
    {
        $cmd = (string) $request->query('cmd', '');
        $building = (int) $request->query('building', 0);
        $listId = (int) $request->query('listid', 0);

        if (!in_array($cmd, ['cancel', 'destroy', 'insert', 'remove'], true)) {
            return redirect('game.php?page=' . $this->page);
        }

        $planetModel = Planets::with(['buildings'])->where('planet_id', $planet['planet_id'])->first();

        if ($planetModel === null) {
            return redirect('game.php?page=' . $this->page);
        }

        if ($cmd === 'insert' && $this->canInitBuildAction($building, $user, $planet)) {
            $this->queueService->add($planetModel, $user, $building, 'build');
        } elseif ($cmd === 'destroy' && $this->canInitBuildAction($building, $user, $planet)) {
            $this->queueService->add($planetModel, $user, $building, 'destroy');
        } elseif ($cmd === 'cancel') {
            $this->queueService->cancelFirst($planetModel, $user);
        } elseif ($cmd === 'remove' && $listId > 1) {
            $this->queueService->removeAt($planetModel, $listId);
        }

        if ($request->query('r') === 'overview') {
            return redirect('game.php?page=overview');
        }

        return redirect('game.php?page=' . $this->page);
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     */
    private function showPage(array $user, array $planet): View
    {
        $planetModel = Planets::with(['buildings'])->where('planet_id', $planet['planet_id'])->first();
        $commanderActive = $this->officerService->isOfficerActive((int) ($user['premium_officier_commander'] ?? 0), time());
        $queueData = $planetModel ? $this->queueService->getQueueData($planetModel) : ['length' => 0, 'to_destroy' => 0, 'items' => []];
        $allowedIds = $this->resolveAllowedBuildings($user, $planet);

        $listOfBuildings = [];
        foreach ($allowedIds as $id) {
            $listOfBuildings[] = $this->buildItem($id, $user, $planet, $queueData, $commanderActive);
        }

        $buildListScript = '';
        $queueRows = [];

        if ($commanderActive && $queueData['length'] > 0) {
            $buildListScript = view('buildings.build_list_script', [
                'call_program' => $this->page,
                'current_page' => $this->page,
            ])->render();
            $queueRows = $this->buildQueueRows($queueData['items'], (int) ($planet['planet_id'] ?? 0));
        }

        return view('buildings.body', [
            'gameTitle' => $this->settingsService->getString('game_name'),
            'list_of_buildings' => $listOfBuildings,
            'BuildListScript' => $buildListScript,
            'queueRows' => $queueRows,
        ]);
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     * @param  array{length:int,items:array<int,array<string,mixed>>}  $queueData
     *
     * @return array<string,mixed>
     */
    private function buildItem(int $id, array $user, array $planet, array $queueData, bool $commanderActive): array
    {
        $level = $this->getBuildingLevel($id, $planet);
        $objName = $this->registry->get($id)->getName();

        return [
            'i' => $id,
            'nivel' => $level > 0 ? ' (' . __('game/global.level') . $level . ')' : '',
            'n' => __('game/constructions.' . $objName),
            'descriptions' => __('game/buildings.descriptions')[$objName],
            'price' => $this->developmentDataService->buildPriceHtml($planet, $id, $level),
            'time' => '<br>' . __('game/buildings.bd_time') . $this->formatService->prettyTime($this->getBuildingTime($id, $planet)),
            'click' => $this->getActionButton($id, $user, $planet, $queueData, $commanderActive),
        ];
    }

    /**
     * @param  array<string,mixed>  $planet
     */
    private function getBuildingLevel(int $buildingId, array $planet): int
    {
        return (int) ($planet[$this->registry->get($buildingId)->getName()] ?? 0);
    }

    /**
     * @param  array<string,mixed>  $planet
     */
    private function getBuildingTime(int $buildingId, array $planet): int
    {
        return $this->developmentsService->developmentTime(
            $buildingId,
            $this->getBuildingLevel($buildingId, $planet),
            $this->getBuildingLevel(BuildingsEnumerator::BUILDING_ROBOT_FACTORY, $planet),
            $this->getBuildingLevel(BuildingsEnumerator::BUILDING_NANO_FACTORY, $planet),
            0,
            0,
            false
        );
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     * @param  array{length:int,items:array<int,array<string,mixed>>}  $queueData
     */
    private function getActionButton(
        int $buildingId,
        array $user,
        array $planet,
        array $queueData,
        bool $commanderActive
    ): string {
        $buildUrl = 'game.php?page=' . $this->page . '&cmd=insert&building=' . $buildingId;
        $queueLength = $queueData['length'];
        $isOnVacation = ((int) ($user['preference_vacation_mode'] ?? 0)) > 0;

        $maxFields = $this->developmentsService->maxFields(
            (int) ($planet['planet_field_max'] ?? 0),
            $this->getBuildingLevel(BuildingsEnumerator::BUILDING_TERRAFORMER, $planet)
        );

        if ((int) ($planet['planet_field_current'] ?? 0) >= $maxFields) {
            return $this->buildButton('all_occupied');
        }

        if ($this->isWorkInProgress($buildingId, $user, $planet)) {
            return $this->buildButton('work_in_progress');
        }

        if ($isOnVacation) {
            return $this->buildButton('not_allowed');
        }

        if ($commanderActive) {
            if ($queueLength >= 5) {
                return $this->buildButton('not_allowed');
            }

            if ($queueLength > 0) {
                return $this->formatService->link($buildUrl, $this->buildButton('allowed_for_queue'));
            }
        } elseif ($queueLength > 0) {
            return $this->buildCountDownClock($buildingId, $queueData);
        }

        if (!$this->developmentsService->isDevelopmentPayable(
            $this->developmentDataService->planetResources($planet),
            $buildingId,
            $this->getBuildingLevel($buildingId, $planet),
            true,
            false,
            0
        )) {
            return $this->buildButton('not_allowed');
        }

        return $this->formatService->link($buildUrl, $this->buildButton('allowed'));
    }

    /** @param array{length:int,items:array<int,array<string,mixed>>} $queueData */
    private function buildCountDownClock(int $buildingId, array $queueData): string
    {
        $firstItem = $queueData['items'][0] ?? null;

        if ($firstItem === null || (int) $firstItem['building_id'] !== $buildingId) {
            return '<center>-</center>';
        }

        $timeLeft = (int) $firstItem['end_time'] - time();

        return view('buildings.build_single_script', [
            'build_time' => $timeLeft,
            'call_program' => $this->page,
        ])->render();
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     */
    private function isWorkInProgress(int $buildingId, array $user, array $planet): bool
    {
        if ($buildingId === BuildingsEnumerator::BUILDING_LABORATORY &&
            $this->developmentsService->isLabWorking((int) ($user['research_current_research'] ?? 0))) {
            return true;
        }

        if (in_array($buildingId, self::WORKING_BUILDINGS, true) &&
            $this->developmentsService->isShipyardWorking((int) ($planet['planet_b_hangar'] ?? 0))) {
            return true;
        }

        return false;
    }

    private function buildButton(string $code): string
    {
        $map = [
            'all_occupied' => ['color' => 'red',   'lang' => 'bd_no_more_fields'],
            'allowed' => ['color' => 'green', 'lang' => 'bd_build'],
            'not_allowed' => ['color' => 'red',   'lang' => 'bd_build'],
            'allowed_for_queue' => ['color' => 'green', 'lang' => 'bd_add_to_list'],
            'work_in_progress' => ['color' => 'red',   'lang' => 'bd_working'],
        ];

        $color = ucfirst($map[$code]['color']);
        $text = __('game/buildings.' . $map[$code]['lang']);
        $method = 'color' . $color;

        return $this->formatService->$method($text);
    }

    /**
     * @param  array<int, array<string,mixed>>  $items
     *
     * @return array<int, array<string, mixed>>
     *
     * @SuppressWarnings("PHPMD.ElseExpression")
     */
    private function buildQueueRows(array $items, int $planetId): array
    {
        $rows = [];

        foreach ($items as $item) {
            $endTime = (int) $item['end_time'];
            $timeLeft = $endTime - time();

            if ($timeLeft <= 0) {
                continue;
            }

            $position = (int) $item['position'];
            $buildingId = (int) $item['building_id'];
            $targetLevel = (int) $item['target_level'];
            $mode = (string) $item['mode'];
            $title = __('game/constructions.' . $this->registry->get($buildingId)->getName());
            $isActive = $position === 1;

            $rows[] = [
                'label' => $mode === 'build'
                    ? $position . '.: ' . $title . ' ' . $targetLevel
                    : $position . '.: ' . $title . ' ' . $targetLevel . ' ' . __('game/buildings.bd_dismantle'),
                'is_active' => $isActive,
                'time_left' => $timeLeft,
                'cancel_url' => 'game.php?page=' . $this->page . '&listid=1&cmd=cancel&planet=' . $planetId,
                'cancel_label' => __('game/buildings.bd_interrupt'),
                'timer_variables' => [
                    'pp' => $timeLeft,
                    'pk' => 1,
                    'pm' => 'cancel',
                    'pl' => $planetId,
                ],
                'finish_at' => $this->timingService->formatExtendedDate($endTime),
                'remove_url' => 'game.php?page=' . $this->page . '&listid=' . $position . '&cmd=remove&planet=' . $planetId,
                'remove_label' => __('game/buildings.bd_cancel'),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     */
    private function canInitBuildAction(int $buildingId, array $user, array $planet): bool
    {
        return in_array($buildingId, $this->resolveAllowedBuildings($user, $planet), true);
    }

    /**
     * @param  array<string,mixed>  $user
     * @param  array<string,mixed>  $planet
     *
     * @return int[]
     */
    private function resolveAllowedBuildings(array $user, array $planet): array
    {
        $planetType = (int) ($planet['planet_type'] ?? 1);
        $raw = $this->setAllowedBuildings($planetType);

        $levels = $this->developmentDataService->levelsFromData($planet, $user);

        return array_values(array_filter(
            $raw,
            fn (int $id) => $this->developmentsService->isDevelopmentAllowed($id, $levels)
        ));
    }
}
