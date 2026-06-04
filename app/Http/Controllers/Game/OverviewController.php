<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Planets;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Objects;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * Bridge migration of the legacy Overview page.
 *
 * Uses Eloquent + the modern App\Services stack for everything new code can
 * own cleanly (other-planets list, fleet movements query, user rank, planet
 * stats). Three legacy Libraries are still called because porting them is a
 * larger scope than this PR:
 *
 *   - Users::getInstance()  — preserved for its side effects: updates
 *     resources / construction queue / research queue on every page load.
 *   - FleetsLib::flyingFleetsTable — generates the HTML row per active
 *     fleet (mission-specific JS countdown wiring).
 *   - DevelopmentsLib::currentBuilding — renders the "cancel building" UI.
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class OverviewController extends BaseController
{
    /**
     * @var array<string, mixed>
     */
    private array $user = [];

    /**
     * @var array<string, mixed>
     */
    private array $planet = [];

    public function __construct(
        private FormatService $formatService,
        private DevelopmentsService $developmentsService,
        private TimingService $timingService,
        private SettingsService $settings,
    ) {
    }

    public function __invoke(): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Overview));

        // Side effects: resource update + construction/research queue progression
        // happen inside Users::getInstance() construction. Keep this until those
        // libraries get ported.
        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();

        $objects = new Objects();
        $moonFields = $this->moonFields();

        return view('overview.view', array_merge(
            [
                'planetName' => $this->planet['planet_name'],
                'username' => $this->user['name'],
                'dateTime' => $this->timingService->formatExtendedDate(time()),
                'newMessage' => $this->newMessageRow(),
                'fleetList' => $this->fleetMovementsHtml(),
                'planetImage' => $this->planet['planet_image'],
                'building' => $this->currentBuildingBlock($this->planet, true, $objects),
                'otherPlanets' => $this->otherPlanetsHtml($objects),
                'planetDiameter' => $this->formatService->prettyNumber((int) $this->planet['planet_diameter']),
                'planetCurrentFields' => $this->planet['planet_field_current'],
                'planetMaxFields' => $this->developmentsService->maxFields(
                    (int) $this->planet['planet_field_max'],
                    (int) $this->planet[$objects->getObjects(33)],
                ),
                'planetMinTemp' => $this->planet['planet_temp_min'],
                'planetMaxTemp' => $this->planet['planet_temp_max'],
                'galaxyGalaxy' => $this->planet['planet_galaxy'],
                'galaxySystem' => $this->planet['planet_system'],
                'galaxyPlanet' => $this->planet['planet_planet'],
                'userRank' => $this->userRankCell(),
            ],
            $moonFields,
        ));
    }

    /**
     * "You have N new message(s)" row, or empty when there are none.
     */
    private function newMessageRow(): string
    {
        $count = (int) ($this->user['new_message'] ?? 0);
        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            $label = (string) __('game/overview.ov_have_new_message');
        } else {
            $template = (string) __('game/overview.ov_have_new_messages');
            $label = str_replace('%m', $this->formatService->prettyNumber($count), $template);
        }

        return '<tr><th role="cell" colspan="4">'
            . $this->formatService->link('game.php?page=messages', $label, $label)
            . '</th></tr>';
    }

    /**
     * Rendered HTML rows for every active fleet involving the user. The legacy
     * UNION query is replaced by a query builder with the same semantics; the
     * HTML rendering still delegates to FleetsLib::flyingFleetsTable so
     * mission-specific JS wiring (`pp`, `pk` countdown vars) stays intact.
     */
    private function fleetMovementsHtml(): string
    {
        $userId = (int) $this->user['id'];
        if ($userId <= 0) {
            return '';
        }

        $own = $this->fleetMovementRows($userId);

        $rowsByTime = [];
        $record = 0;

        foreach ($own as $f) {
            $startTime = (int) $f['fleet_start_time'];
            $stayTime = (int) $f['fleet_end_stay'];
            $endTime = (int) $f['fleet_end_time'];
            $mission = (int) $f['fleet_mission'];
            $status = (int) $f['fleet_mess'];
            $id = (int) $f['fleet_id'];
            $isOwn = (int) $f['fleet_owner'] === $userId;

            if ($isOwn) {
                $record++;
                $this->stash($rowsByTime, $startTime . $id);
                $this->stash($rowsByTime, $stayTime . $id);
                $this->stash($rowsByTime, $endTime . $id);

                if ($startTime > time()) {
                    $rowsByTime[$startTime . $id] = FleetsLib::flyingFleetsTable($f, 0, true, 'fs', $record, $this->user);
                }

                if ($mission !== 4 && $mission !== 10) {
                    if ($stayTime > time()) {
                        $rowsByTime[$stayTime . $id] = FleetsLib::flyingFleetsTable($f, 1, true, 'ft', $record, $this->user);
                    }
                    if ($endTime > time()) {
                        $rowsByTime[$endTime . $id] = FleetsLib::flyingFleetsTable($f, 2, true, 'fe', $record, $this->user);
                    }
                }

                if ($mission === 4 && $startTime < time() && $endTime > time()) {
                    $rowsByTime[$endTime . $id] = FleetsLib::flyingFleetsTable($f, 2, true, 'none', $record, $this->user);
                }
                continue;
            }

            // Incoming attack / ACS attack on the user
            if ($mission === 2 || ($mission === 1 && (int) $f['fleet_group'] > 0)) {
                $record++;
                $effectiveStart = $status > 0 ? '' : $startTime;
                $key = $effectiveStart . $id;
                $this->stash($rowsByTime, $key);

                if ($effectiveStart !== '' && $effectiveStart > time()) {
                    $rowsByTime[$key] = FleetsLib::flyingFleetsTable($f, 0, false, 'ofs', $record, $this->user);
                }
                continue;
            }

            // Other foreign fleets that aren't lurking probes (mission 8)
            if ($mission !== 8) {
                $record++;
                $acsMember = $this->isAcsMember($f);

                $this->stash($rowsByTime, $startTime . $id);
                $this->stash($rowsByTime, $stayTime . $id);

                if ($startTime > time()) {
                    $rowsByTime[$startTime . $id] = FleetsLib::flyingFleetsTable($f, 0, false, 'ofs', $record, $this->user, $acsMember);
                }
                if ($mission === 5 && $stayTime > time()) {
                    $rowsByTime[$stayTime . $id] = FleetsLib::flyingFleetsTable($f, 1, false, 'oft', $record, $this->user, $acsMember);
                }
            }
        }

        if (empty($rowsByTime)) {
            return '';
        }

        ksort($rowsByTime);

        return implode("\n", array_filter($rowsByTime)) . "\n";
    }

    /**
     * Active fleets touching the user — own, attacks against them, and ACS
     * groups they belong to. Replaces the legacy hand-written UNION.
     *
     * @return list<array<string, mixed>>
     */
    private function fleetMovementRows(int $userId): array
    {
        $ownOrTarget = DB::table('fleets')
            ->where('fleet_owner', $userId)
            ->orWhere('fleet_target_owner', $userId);

        $acsFleets = DB::table('acs_members')
            ->join('fleets', 'fleets.fleet_group', '=', 'acs_members.acs_group_id')
            ->where('acs_members.acs_user_id', $userId)
            ->select('fleets.*');

        $fleetsUnion = $ownOrTarget->select('fleets.*')->unionAll($acsFleets);

        $rows = DB::query()
            ->fromSub($fleetsUnion, 'fleets')
            ->distinct()
            ->join('users as uo', 'uo.id', '=', 'fleets.fleet_owner')
            ->leftJoin('users as ut', 'ut.id', '=', 'fleets.fleet_target_owner')
            ->join('planets as po', function ($join): void {
                $join->on('po.planet_galaxy', '=', 'fleets.fleet_start_galaxy')
                    ->on('po.planet_system', '=', 'fleets.fleet_start_system')
                    ->on('po.planet_planet', '=', 'fleets.fleet_start_planet')
                    ->on('po.planet_type', '=', 'fleets.fleet_start_type');
            })
            ->leftJoin('planets as pt', function ($join): void {
                $join->on('pt.planet_galaxy', '=', 'fleets.fleet_end_galaxy')
                    ->on('pt.planet_system', '=', 'fleets.fleet_end_system')
                    ->on('pt.planet_planet', '=', 'fleets.fleet_end_planet')
                    ->on('pt.planet_type', '=', 'fleets.fleet_end_type');
            })
            ->select(
                'fleets.*',
                'po.planet_name as start_planet_name',
                'pt.planet_name as target_planet_name',
                'uo.name as start_planet_user',
                'ut.name as target_planet_user',
                DB::raw('(SELECT GROUP_CONCAT(am.acs_user_id) FROM acs_members am WHERE am.acs_group_id = fleets.fleet_group) AS acs_members'),
            )
            ->get();

        return $rows->map(fn ($row) => (array) $row)->all();
    }

    /**
     * @param array<string, string> $rowsByTime
     */
    private function stash(array &$rowsByTime, string $key): void
    {
        if (!isset($rowsByTime[$key])) {
            $rowsByTime[$key] = '';
        }
    }

    /**
     * @param array<string, mixed> $fleet
     */
    private function isAcsMember(array $fleet): bool
    {
        $members = explode(',', (string) ($fleet['acs_members'] ?? ''));

        return \in_array((string) $this->user['id'], $members, true);
    }

    /**
     * Current-construction cell for a planet. Delegates HTML to DevelopmentsLib
     * for the "current planet" case (cancel button + countdown JS) and renders
     * the inert summary inline for the other-planets sidebar.
     *
     * @param array<string, mixed> $userPlanet
     */
    private function currentBuildingBlock(array $userPlanet, bool $isCurrent, Objects $objects): string
    {
        if ((int) ($userPlanet['planet_b_building'] ?? 0) === 0) {
            return (string) __('game/overview.ov_free');
        }

        $queue = explode(';', (string) $userPlanet['planet_b_building_id']);
        $currentItem = explode(',', $queue[0]);
        $buildingId = (int) ($currentItem[0] ?? 0);
        $level = (int) ($currentItem[1] ?? 0);
        $timeToEnd = (int) ($currentItem[3] ?? 0) - time();

        $label = (string) __('game/constructions.' . $objects->getObjects($buildingId));

        if (!$isCurrent) {
            return $label . ' (' . $level . ')'
                . '<br><font color="#7f7f7f">(' . $this->formatService->prettyTime($timeToEnd) . ')</font>';
        }

        // Current planet: render the cancel UI + JS countdown wiring inherited
        // from the legacy DevelopmentsLib. Reproduced here for the script block
        // because the legacy version embeds it inline.
        $block = DevelopmentsLib::currentBuilding('overview', $buildingId);
        $block .= $label . ' (' . $level . ')';
        $block .= '<br><div id="blc" class="z">' . $this->formatService->prettyTime($timeToEnd) . '</div>';
        $block .= "\n<script language=\"JavaScript\">\n"
            . "\tpp = \"" . $timeToEnd . "\";\n"
            . "\tpk = \"1\";\n"
            . "\tpm = \"cancel\";\n"
            . "\tpl = \"" . $this->planet['planet_id'] . "\";\n"
            . "\tt();\n"
            . "</script>\n";

        return $block;
    }

    /**
     * Sidebar of the user's other colonies (excludes current planet + moons).
     */
    private function otherPlanetsHtml(Objects $objects): string
    {
        $userId = (int) $this->user['id'];
        $currentPlanetId = (int) ($this->user['current_planet'] ?? 0);

        if ($userId <= 0) {
            return '<tr></tr>';
        }

        $planets = Planets::query()
            ->join('buildings', 'buildings.building_planet_id', '=', 'planets.planet_id')
            ->join('defenses', 'defenses.defense_planet_id', '=', 'planets.planet_id')
            ->join('ships', 'ships.ship_planet_id', '=', 'planets.planet_id')
            ->where('planets.planet_user_id', $userId)
            ->where('planets.planet_destroyed', 0)
            ->where('planets.planet_id', '!=', $currentPlanetId)
            ->where('planets.planet_type', '!=', PlanetTypesEnumerator::MOON)
            ->select('planets.*', 'buildings.*', 'defenses.*', 'ships.*')
            ->get();

        $column = 1;
        $html = '<tr>';

        foreach ($planets as $planet) {
            $row = (array) $planet->toArray();
            $url = 'game.php?page=overview&cp=' . $planet->planet_id . '&re=0';
            $image = asset('assets/upload/skins/xgproyect/planets/small/s_' . $planet->planet_image . '.jpg');

            $html .= '<th>' . $planet->planet_name . '<br>';
            $html .= $this->formatService->link(
                $url,
                Functions::setImage($image, $planet->planet_name, 'height="50" width="50"'),
            );
            $html .= '<center>' . $this->currentBuildingBlock($row, false, $objects) . '</center>';
            $html .= '</th>';

            if ($column < 2) {
                $column++;
            } else {
                $html .= '</tr><tr>';
                $column = 1;
            }
        }

        $html .= '</tr>';

        return $html;
    }

    /**
     * Moon row (image + link). Empty when the current planet has no associated
     * moon or the moon is destroyed / current view is already a moon.
     *
     * @return array{moonImg: string, moon: string}
     */
    private function moonFields(): array
    {
        $hasMoon = (int) ($this->planet['moon_id'] ?? 0) !== 0
            && (int) ($this->planet['moon_destroyed'] ?? 0) === 0
            && (int) $this->planet['planet_type'] === PlanetTypesEnumerator::PLANET;

        if (!$hasMoon) {
            return ['moonImg' => '', 'moon' => ''];
        }

        $moonName = $this->planet['moon_name'] . ' (' . __('game/global.moon') . ')';
        $url = 'game.php?page=overview&cp=' . $this->planet['moon_id'] . '&re=0';
        $image = asset('assets/upload/skins/xgproyect/planets/' . $this->planet['moon_image'] . '.jpg');

        return [
            'moonImg' => $this->formatService->link(
                $url,
                Functions::setImage($image, $moonName, 'height="50" width="50"'),
                $moonName,
            ),
            'moon' => $moonName,
        ];
    }

    /**
     * User rank cell. Hidden for accounts above stat_admin_level (replaces
     * NoobsProtectionLib::isRankVisible — single comparison, no need to drag
     * the library).
     */
    private function userRankCell(): string
    {
        if ((int) $this->user['authlevel'] > $this->settings->getInt('stat_admin_level')) {
            return '-';
        }

        $totalRank = $this->user['user_statistic_total_rank'] === ''
            ? $this->planet['stats_users']
            : $this->user['user_statistic_total_rank'];

        return (string) __('game/overview.ov_place', [
            'points' => $this->formatService->prettyNumber((int) $this->user['user_statistic_total_points']),
            'url' => $this->formatService->link(
                'game.php?page=statistics&range=' . $totalRank,
                (string) $totalRank,
                (string) $totalRank,
            ),
            'total' => $this->planet['stats_users'],
        ]);
    }
}
