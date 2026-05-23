<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Enums\Module;
use App\Models\Planets;
use App\Models\ResearchQueue;
use App\Models\User;
use App\Services\FormatService;
use App\Services\Game\DevelopmentDataService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\ResearchQueueService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\ResearchEnumerator;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class ResearchController extends BaseController
{
    private const OFFICER_BONUSES = [
        ResearchEnumerator::research_espionage_technology => [
            'officer' => 'premium_officier_technocrat',
            'const' => TECHNOCRATE_SPY,
            'lang' => 're_spy',
        ],
        ResearchEnumerator::research_computer_technology => [
            'officer' => 'premium_officier_admiral',
            'const' => AMIRAL,
            'lang' => 're_commander',
        ],
    ];

    public function __construct(
        private GameObjectRegistry $registry,
        private DevelopmentsService $developmentsService,
        private FormatService $formatService,
        private DevelopmentDataService $developmentDataService,
        private OfficerService $officerService,
        private SettingsService $settingsService,
        private ResearchQueueService $researchQueueService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(Request $request): View | RedirectResponse
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Research));

        $userData = Users::getInstance()->getUserData();
        $planetData = Users::getInstance()->getPlanetData();

        if ($request->query('cmd') !== null) {
            return $this->handleAction($request, $userData, $planetData);
        }

        return $this->showPage($userData, $planetData);
    }

    /**
     * @param  array<string,mixed>  $userData
     * @param  array<string,mixed>  $planetData
     */
    private function handleAction(Request $request, array $userData, array $planetData): RedirectResponse
    {
        $cmd = (string) $request->query('cmd', '');
        $techId = (int) $request->query('tech', 0);

        $userModel = User::with(['research', 'researchQueue'])->find((int) ($userData['id'] ?? 0));
        $planetModel = Planets::with(['buildings'])->where('planet_id', $planetData['planet_id'])->first();

        if ($userModel === null || $planetModel === null) {
            return redirect('game.php?page=research');
        }

        if ($cmd === 'search' && $this->registry->has($techId) && $this->registry->research()->has($techId)) {
            $technocrateActive = $this->officerService->isOfficerActive(
                (int) ($userData['premium_officier_technocrat'] ?? 0),
                time()
            );
            $this->researchQueueService->add($userModel, $planetModel, $userData, $techId, $technocrateActive);
        } elseif ($cmd === 'cancel') {
            $this->researchQueueService->cancel($userModel);
        } elseif ($cmd === 'remove') {
            $position = (int) $request->query('position', 0);
            $this->researchQueueService->removeAt($userModel, $position);
        }

        return redirect('game.php?page=research');
    }

    /**
     * @param  array<string,mixed>  $userData
     * @param  array<string,mixed>  $planetData
     *
     * @SuppressWarnings("PHPMD.NPathComplexity")
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    private function showPage(array $userData, array $planetData): View
    {
        $userModel = User::with(['research', 'researchQueue'])->find((int) ($userData['id'] ?? 0));
        $planetModel = Planets::with(['buildings', 'buildingQueue'])->where('planet_id', $planetData['planet_id'])->first();

        $labCol = $this->registry->get(BuildingsEnumerator::BUILDING_LABORATORY)->getName();
        $labLevel = (int) ($planetData[$labCol] ?? 0);

        if ($labLevel === 0) {
            Functions::message(__('game/research.re_lab_required'), '', 0, true);
        }

        if ($userModel === null || $planetModel === null) {
            return view('research.view', [
                'gameTitle' => $this->settingsService->getString('game_name'),
                'noresearch' => '',
                'queueScript' => '',
                'queueRows' => [],
                'technologies' => [],
            ]);
        }

        $queue = $userModel->researchQueue()->orderBy('position')->get();
        $activeItem = $queue->firstWhere('position', 1);
        $isWorking = $activeItem !== null;
        $queueCount = $queue->count();

        $labInQueue = $planetModel->buildingQueue()->where('building_id', BuildingsEnumerator::BUILDING_LABORATORY)->exists();
        $isOnVacation = (int) ($userData['preference_vacation_mode'] ?? 0) > 0;
        $commanderActive = $this->officerService->isOfficerActive(
            (int) ($userData['premium_officier_commander'] ?? 0),
            time()
        );
        $technocrateActive = $this->officerService->isOfficerActive(
            (int) ($userData['premium_officier_technocrat'] ?? 0),
            time()
        );

        $totalLabLevel = $this->researchQueueService->labLevel($userModel, $planetModel);
        $astrophysicsCol = $this->registry->get(ResearchEnumerator::research_astrophysics)->getName();
        $astrophysicsLevel = (int) ($userData[$astrophysicsCol] ?? 0);

        $workingPlanet = null;
        if ($isWorking && $activeItem->planet_id !== $planetModel->planet_id) {
            $workingPlanet = Planets::find($activeItem->planet_id);
        }

        $levels = $this->developmentDataService->levelsFromPlanet($planetModel, $userData);

        $technologies = [];
        foreach ($this->registry->research()->keys()->all() as $techId) {
            if (!$this->developmentsService->isDevelopmentAllowed($techId, $levels)) {
                continue;
            }

            $techCol = $this->registry->get($techId)->getName();
            $currentLevel = (int) ($userData[$techCol] ?? 0);
            $duration = $this->developmentsService->developmentTime(
                $techId,
                $currentLevel,
                0,
                0,
                $totalLabLevel,
                $astrophysicsLevel,
                $technocrateActive
            );

            $technologies[] = [
                'tech_id' => $techId,
                'tech_level' => $this->formatLevel($techId, $currentLevel, $userData),
                'tech_name' => __('game/technologies.' . $techCol),
                'tech_descr' => __('game/research.descriptions')[$techCol],
                'tech_price' => $this->developmentDataService->buildPriceHtml($planetData, $techId, $currentLevel),
                'search_time' => '<br>' . __('game/research.re_time') . $this->formatService->prettyTime($duration),
                'tech_link' => $this->getActionLink(
                    $techId,
                    $isWorking,
                    $queueCount,
                    $labInQueue,
                    $isOnVacation,
                    $commanderActive,
                    $activeItem,
                    $planetModel,
                    $workingPlanet,
                    $currentLevel
                ),
            ];
        }

        $queueItems = $queue->all();
        $queueScript = ($commanderActive && !empty($queueItems)) ? view('research.queue_script')->render() : '';
        $queueRows = $commanderActive ? $this->buildQueueRows($queueItems) : [];

        return view('research.view', [
            'gameTitle' => $this->settingsService->getString('game_name'),
            'noresearch' => $labInQueue ? __('game/research.re_building_lab') : '',
            'queueScript' => $queueScript,
            'queueRows' => $queueRows,
            'technologies' => $technologies,
        ]);
    }

    /**
     * @param  array<string,mixed>  $userData
     */
    private function formatLevel(int $techId, int $level, array $userData): string
    {
        $text = $level > 0 ? ' (' . __('game/global.level') . $level . ')' : '';

        if (isset(self::OFFICER_BONUSES[$techId])) {
            $bonus = self::OFFICER_BONUSES[$techId];
            if ($this->officerService->isOfficerActive((int) ($userData[$bonus['officer']] ?? 0), time())) {
                $text .= $this->formatService->strongText(
                    $this->formatService->colorGreen(' +' . $bonus['const'] . __('game/research.' . $bonus['lang']))
                );
            }
        }

        return $text;
    }

    private function getActionLink(
        int $techId,
        bool $isWorking,
        int $queueCount,
        bool $labInQueue,
        bool $isOnVacation,
        bool $commanderActive,
        ?ResearchQueue $activeItem,
        Planets $planet,
        ?Planets $workingPlanet,
        int $currentLevel,
    ): string {
        if ($isWorking && !$commanderActive && $activeItem !== null && $activeItem->tech_id === $techId) {
            return $this->buildCountdown($activeItem, $planet, $workingPlanet);
        }

        if ($labInQueue || $isOnVacation) {
            return $this->formatService->colorRed(__('game/research.re_research'));
        }

        if ($isWorking) {
            if (!$commanderActive || $queueCount >= ResearchQueueService::MAX_QUEUE_SIZE) {
                return '<center>-</center>';
            }

            return $this->formatService->link(
                'game.php?page=research&cmd=search&tech=' . $techId,
                $this->formatService->colorGreen(__('game/research.re_research'))
            );
        }

        if (!$this->developmentsService->isDevelopmentPayable(
            $this->developmentDataService->planetResources($planet),
            $techId,
            $currentLevel
        )) {
            return $this->formatService->colorRed(__('game/research.re_research'));
        }

        return $this->formatService->link(
            'game.php?page=research&cmd=search&tech=' . $techId,
            $this->formatService->colorGreen(__('game/research.re_research'))
        );
    }

    private function buildCountdown(ResearchQueue $item, Planets $planet, ?Planets $workingPlanet): string
    {
        $timeLeft = $item->end_time - time();
        $homePlanet = $workingPlanet ?? $planet;

        $techName = '';
        if ($workingPlanet !== null) {
            $techName = __('game/research.re_from') . $workingPlanet->planet_name . '<br> '
                . $this->formatService->prettyCoords(
                    $workingPlanet->planet_galaxy,
                    $workingPlanet->planet_system,
                    $workingPlanet->planet_planet
                );
        }

        return view('research.script', [
            'tech_time' => $timeLeft,
            'tech_name' => $techName,
            'tech_home' => $homePlanet->planet_id,
            'tech_id' => $item->tech_id,
        ])->render();
    }

    /**
     * @param  array<int, ResearchQueue>  $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildQueueRows(array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $techCol = $this->registry->get($item->tech_id)->getName();
            $techName = __('game/technologies.' . $techCol);
            $timeLeft = max(0, $item->end_time - time());
            $isActive = $item->position === 1;

            $rows[] = [
                'label' => $item->position . '.: ' . $techName . ' ' . $item->target_level,
                'is_active' => $isActive,
                'time_left' => $timeLeft,
                'cancel_url' => 'game.php?page=research&cmd=cancel',
                'cancel_label' => __('game/research.re_cancel'),
                'timer_variables' => [
                    'pp' => $timeLeft,
                ],
                'finish_at' => $this->timingService->formatExtendedDate($item->end_time),
                'remove_url' => 'game.php?page=research&cmd=remove&position=' . $item->position,
                'remove_label' => __('game/research.re_cancel'),
            ];
        }

        return $rows;
    }
}
