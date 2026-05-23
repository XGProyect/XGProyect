<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\NoobsProtectionLib;
use Xgp\App\Libraries\UpdatesLibrary;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class OverviewController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];
    private NoobsProtectionLib $noob;
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private DevelopmentsService $developmentsService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Overview));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->noob = new NoobsProtectionLib();
        $this->objects = new Objects();

        Template::legacyView(
            'overview.view',
            array_merge(
                [
                    'planetName' => $this->planet['planet_name'],
                    'username' => $this->user['name'],
                    'dateTime' => $this->timingService->formatExtendedDate(time()),
                    'newMessage' => $this->getMessages(),
                    'fleetList' => $this->getFleetMovements(),
                    'planetImage' => $this->planet['planet_image'],
                    'building' => $this->getCurrentWork($this->planet),
                    'otherPlanets' => $this->getPlanets(),
                    'planetDiameter' => $this->formatService->prettyNumber((int) $this->planet['planet_diameter']),
                    'planetCurrentFields' => $this->planet['planet_field_current'],
                    'planetMaxFields' => $this->developmentsService->maxFields((int) $this->planet['planet_field_max'], (int) $this->planet[$this->objects->getObjects(33)]),
                    'planetMinTemp' => $this->planet['planet_temp_min'],
                    'planetMaxTemp' => $this->planet['planet_temp_max'],
                    'galaxyGalaxy' => $this->planet['planet_galaxy'],
                    'galaxySystem' => $this->planet['planet_system'],
                    'galaxyPlanet' => $this->planet['planet_planet'],
                    'userRank' => $this->getUserRank(),
                ],
                $this->getPlanetMoon()
            )
        );
    }

    private function getCurrentWork(array $user_planet, $is_current_planet = true): string
    {
        // THE PLANET IS "FREE" BY DEFAULT
        $building_block = __('game/overview.ov_free');

        if (!$is_current_planet) {
            // UPDATE THE PLANET INFORMATION FIRST, MAY BE SOMETHING HAS JUST FINISHED
            UpdatesLibrary::updateBuildingsQueue($user_planet, $this->user);
        }

        if ($user_planet['planet_b_building'] != 0) {
            if ($user_planet['planet_b_building'] != 0) {
                $queue = explode(';', $user_planet['planet_b_building_id']); // GET ALL
                $current_building = explode(',', $queue[0]); // GET ONLY THE FIRST ELEMENT
                $building = (int) $current_building[0]; // THE BUILDING
                $level = (int) $current_building[1]; // THE LEVEL
                $time_to_end = $current_building[3] - time(); // THE TIME

                // THE BUILDING BLOCK
                if ($is_current_planet) {
                    $building_block = DevelopmentsLib::currentBuilding('overview', $building);
                    $building_block .= __('game/constructions.' . $this->objects->getObjects($building)) . ' (' . $level . ')';
                    $building_block .= '<br><div id="blc" class="z">' . $this->formatService->prettyTime($time_to_end) . '</div>';
                    $building_block .= "\n<script language=\"JavaScript\">";
                    $building_block .= "\n	pp = \"" . $time_to_end . "\";\n";
                    $building_block .= "\n	pk = \"" . 1 . "\";\n";
                    $building_block .= "\n	pm = \"cancel\";\n";
                    $building_block .= "\n	pl = \"" . $this->planet['planet_id'] . "\";\n";
                    $building_block .= "\n	t();\n";
                    $building_block .= "\n</script>\n";
                } else {
                    $building_block = '' . __('game/constructions.' . $this->objects->getObjects($building)) . ' (' . $level . ')';
                    $building_block .= '<br><font color="#7f7f7f">(' . $this->formatService->prettyTime($time_to_end) . ')</font>';
                }
            }
        }

        return $building_block;
    }

    private function getMessages(): string
    {
        $new_message = '';

        if ($this->user['new_message'] != 0) {
            $new_message = '<tr>';

            if ($this->user['new_message'] == 1) {
                $new_message .= '<th role="cell" colspan="4">' . $this->formatService->link('game.php?page=messages', __('game/overview.ov_have_new_message'), __('game/overview.ov_have_new_message')) . '</th>';
            }

            if ($this->user['new_message'] > 1) {
                $messageTemplate = __('game/overview.ov_have_new_messages');

                if (!is_string($messageTemplate)) {
                    $messageTemplate = '';
                }

                $linkText = str_replace('%m', $this->formatService->prettyNumber((int) $this->user['new_message']), $messageTemplate);
                $new_message .= '<th role="cell" colspan="4">' . $this->formatService->link('game.php?page=messages', $linkText, $linkText) . '</th>';
            }

            $new_message .= '</tr>';
        }

        return $new_message;
    }

    private function getFleetMovements(): string
    {
        $fleet = '';
        $fleet_row = [];
        $record = 0;

        $userId = (int) $this->user['id'];
        $own_fleets = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT DISTINCT
                        fleets.*,
                        po.`planet_name` AS `start_planet_name`,
                        pt.`planet_name` AS `target_planet_name`,
                        uo.`name` AS `start_planet_user`,
                        ut.`name` AS `target_planet_user`,
                        (
                            SELECT
                                GROUP_CONCAT(am.`acs_user_id`)
                            FROM `' . ACS_MEMBERS . '` am
                            WHERE am.`acs_group_id` = fleets.`fleet_group`
                        ) AS `acs_members`
                    FROM
                    (
                        SELECT
                            f.*
                        FROM
                            `' . FLEETS . "` f
                        WHERE
                            f.`fleet_owner` = '" . $userId . "'
                        OR
                            f.`fleet_target_owner` = '" . $userId . "'
                        UNION ALL
                        SELECT
                            f.*
                        FROM
                            `" . ACS_MEMBERS . '` am
                        LEFT JOIN `' . FLEETS . "` f ON
                            f.`fleet_group` = am.`acs_group_id`
                        WHERE
                            f.`fleet_id` IS NOT NULL
                        AND
                            am.`acs_user_id` = '" . $userId . "'
                    ) fleets
                    INNER JOIN `" . USERS . '` uo ON
                        uo.`id` = fleets.`fleet_owner`
                    LEFT JOIN `' . USERS . '` ut ON
                        ut.`id` = fleets.`fleet_target_owner`
                    INNER JOIN `' . PLANETS . '` po ON
                    (
                        po.`planet_galaxy` = fleets.`fleet_start_galaxy` AND
                        po.`planet_system` = fleets.`fleet_start_system` AND
                        po.`planet_planet` = fleets.`fleet_start_planet` AND
                        po.`planet_type` = fleets.`fleet_start_type`
                    )
                    LEFT JOIN `' . PLANETS . '` pt ON
                    (
                        pt.`planet_galaxy` = fleets.`fleet_end_galaxy` AND
                        pt.`planet_system` = fleets.`fleet_end_system` AND
                        pt.`planet_planet` = fleets.`fleet_end_planet` AND
                        pt.`planet_type` = fleets.`fleet_end_type`
                    )'
                )
            )
        ) : null;

        foreach ($own_fleets as $fleets) {
            //#####################################
            //
            // own fleets
            //
            //#####################################

            $start_time = $fleets['fleet_start_time'];
            $stay_time = $fleets['fleet_end_stay'];
            $end_time = $fleets['fleet_end_time'];

            $fleet_status = $fleets['fleet_mess'];
            $fleet_group = $fleets['fleet_group'];
            $id = $fleets['fleet_id'];

            if ($fleets['fleet_owner'] == $this->user['id']) {
                $record++;

                $label = 'fs';
                $start_block_id = (string) $start_time . $id;
                $stay_block_id = (string) $stay_time . $id;
                $end_block_id = (string) $end_time . $id;

                $fleet_row[$start_block_id] = !isset($fleet_row[$start_block_id]) ? '' : $fleet_row[$start_block_id];
                $fleet_row[$stay_block_id] = !isset($fleet_row[$stay_block_id]) ? '' : $fleet_row[$stay_block_id];
                $fleet_row[$end_block_id] = !isset($fleet_row[$end_block_id]) ? '' : $fleet_row[$end_block_id];

                if ($start_time > time()) {
                    $fleet_row[$start_block_id] = FleetsLib::flyingFleetsTable($fleets, 0, true, $label, $record, $this->user);
                }

                if (($fleets['fleet_mission'] != 4) && ($fleets['fleet_mission'] != 10)) {
                    $label = 'ft';

                    if ($stay_time > time()) {
                        $fleet_row[$stay_block_id] = FleetsLib::flyingFleetsTable($fleets, 1, true, $label, $record, $this->user);
                    }

                    $label = 'fe';

                    if ($end_time > time()) {
                        $fleet_row[$end_block_id] = FleetsLib::flyingFleetsTable($fleets, 2, true, $label, $record, $this->user);
                    }
                }

                if ($fleets['fleet_mission'] == 4 && $start_time < time() && $end_time > time()) {
                    $fleet_row[$end_block_id] = FleetsLib::flyingFleetsTable($fleets, 2, true, 'none', $record, $this->user);
                }
            }

            //#####################################
            //
            // incoming fleets
            //
            //#####################################
            if ($fleets['fleet_owner'] != $this->user['id']) {
                if ($fleets['fleet_mission'] == 2) {
                    $record++;
                    $start_time = ($fleet_status > 0) ? '' : $fleets['fleet_start_time'];

                    $start_block_id = (string) $start_time . $id;
                    $fleet_row[$start_block_id] = !isset($fleet_row[$start_block_id]) ? '' : $fleet_row[$start_block_id];

                    if ($start_time > time()) {
                        $fleet_row[$start_block_id] = FleetsLib::flyingFleetsTable(
                            $fleets,
                            0,
                            false,
                            'ofs',
                            $record,
                            $this->user
                        );
                    }
                }

                if (($fleets['fleet_mission'] == 1) && ($fleet_group > 0)) {
                    $record++;

                    if ($fleet_status > 0) {
                        $start_time = '';
                    } else {
                        $start_time = $fleets['fleet_start_time'];
                    }

                    $start_block_id = (string) $start_time . $id;
                    $fleet_row[$start_block_id] = !isset($fleet_row[$start_block_id]) ? '' : $fleet_row[$start_block_id];

                    if ($start_time > time()) {
                        $fleet_row[$start_block_id] = FleetsLib::flyingFleetsTable($fleets, 0, false, 'ofs', $record, $this->user);
                    }
                }
            }

            //#####################################
            //
            // other fleets
            //
            //#####################################

            if ($fleets['fleet_owner'] != $this->user['id']) {
                $acs_member = false;

                if (in_array($this->user['id'], explode(',', $fleets['acs_members'] ?? ''))) {
                    $acs_member = true;
                }

                if ($fleets['fleet_mission'] != 8) {
                    $record++;

                    $start_time = $fleets['fleet_start_time'];
                    $stay_time = $fleets['fleet_end_stay'];
                    $id = $fleets['fleet_id'];

                    $start_block_id = (string) $start_time . $id;
                    $stay_block_id = (string) $stay_time . $id;

                    $fleet_row[$start_block_id] = !isset($fleet_row[$start_block_id]) ? '' : $fleet_row[$start_block_id];
                    $fleet_row[$stay_block_id] = !isset($fleet_row[$stay_block_id]) ? '' : $fleet_row[$stay_block_id];

                    if ($start_time > time()) {
                        $fleet_row[$start_block_id] = FleetsLib::flyingFleetsTable($fleets, 0, false, 'ofs', $record, $this->user, $acs_member);
                    }
                    if ($fleets['fleet_mission'] == 5) {
                        if ($stay_time > time()) {
                            $fleet_row[$stay_block_id] = FleetsLib::flyingFleetsTable($fleets, 1, false, 'oft', $record, $this->user, $acs_member);
                        }
                    }
                }
            }
        }

        unset($own_fleets);

        if (count($fleet_row) > 0 && $fleet_row != '') {
            ksort($fleet_row);

            foreach ($fleet_row as $time => $content) {
                $fleet .= $content . "\n";
            }

            unset($fleet_row);
        }

        return $fleet;
    }

    private function getPlanetMoon(): array
    {
        $return['moonImg'] = '';
        $return['moon'] = '';

        if ($this->planet['moon_id'] != 0 && $this->planet['moon_destroyed'] == 0 && $this->planet['planet_type'] == PlanetTypesEnumerator::PLANET) {
            $moon_name = $this->planet['moon_name'] . ' (' . __('game/global.moon') . ')';
            $url = 'game.php?page=overview&cp=' . $this->planet['moon_id'] . '&re=0';
            $image = asset('assets/upload/skins/xgproyect/planets/' . $this->planet['moon_image'] . '.jpg');
            $attributes = 'height="50" width="50"';

            $return['moonImg'] = $this->formatService->link($url, Functions::setImage($image, $moon_name, $attributes), $moon_name);
            $return['moon'] = $moon_name;
        }

        return $return;
    }

    private function getPlanets(): string
    {
        $colony = 1;

        $userId = (int) $this->user['id'];
        $planets_query = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT *
                        FROM ' . PLANETS . ' AS p
                        INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
                        INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                        INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id`
                        WHERE `planet_user_id` = '" . $userId . "'
                                AND `planet_destroyed` = 0;"
                )
            )
        ) : null;
        $planet_block = '<tr>';

        foreach ($planets_query as $user_planet) {
            if ($user_planet['planet_id'] != $this->user['current_planet'] && $user_planet['planet_type'] != PlanetTypesEnumerator::MOON) {
                $url = 'game.php?page=overview&cp=' . $user_planet['planet_id'] . '&re=0';
                $image = asset('assets/upload/skins/xgproyect/planets/small/s_' . $user_planet['planet_image'] . '.jpg');
                $attributes = 'height="50" width="50"';

                $planet_block .= '<th>' . $user_planet['planet_name'] . '<br>';
                $planet_block .= $this->formatService->link($url, Functions::setImage($image, $user_planet['planet_name'], $attributes));
                $planet_block .= '<center>';
                $planet_block .= $this->getCurrentWork($user_planet, false);
                $planet_block .= '</center></th>';

                if ($colony <= 1) {
                    $colony++;
                } else {
                    $planet_block .= '</tr><tr>';
                    $colony = 1;
                }
            }
        }

        $planet_block .= '</tr>';

        unset($planets_query);

        return $planet_block;
    }

    private function getUserRank(): string
    {
        $userRank = '-';
        $totalRank = $this->user['user_statistic_total_rank'] == '' ? $this->planet['stats_users'] : $this->user['user_statistic_total_rank'];

        if ($this->noob->isRankVisible((int) $this->user['authlevel'])) {
            $userRank = __('game/overview.ov_place', [
                'points' => $this->formatService->prettyNumber((int) $this->user['user_statistic_total_points']),
                'url' => $this->formatService->link(
                    'game.php?page=statistics&range=' . $totalRank,
                    (string) $totalRank,
                    (string) $totalRank
                ),
                'total' => $this->planet['stats_users'],
            ]);
        }

        return $userRank;
    }
}
