<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Support\Facades\View;
use Xgp\App\Core\BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\NoobsProtectionLib;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\UpdatesLibrary;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Overview;

class OverviewController extends BaseController
{
    public const MODULE_ID = 1;

    private Overview $overviewModel;
    private NoobsProtectionLib $noob;

    public function __construct()
    {
        parent::__construct();

        Users::checkSession();

        // load Language
        parent::loadLang(['game/global', 'game/overview', 'game/buildings', 'game/constructions']);

        $this->overviewModel = new Overview();
        $this->noob = new NoobsProtectionLib();
    }

    public function __invoke(): void
    {
        // Check module access
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $moon = $this->getPlanetMoon();

        Template::getInstance()->view(
            'overview.body',
            [
                'dpath' => DPATH,
                'planetName' => $this->planet['planet_name'],
                'username' => $this->user['user_name'],
                'dateTime' => Timing::formatExtendedDate(time()),
                'newMessage' => $this->getMessages(),
                'fleetList' => $this->getFleetMovements(),
                'planetImage' => $this->planet['planet_image'],
                'building' => $this->getCurrentWork($this->planet),
                'moonImg' => $moon['moon_img'],
                'moon' => $moon['moon'],
                'otherPlanets' => $this->getPlanets(),
                'planetDiameter' => FormatLib::prettyNumber($this->planet['planet_diameter']),
                'planetCurrentFields' => $this->planet['planet_field_current'],
                'planetMaxFields' => DevelopmentsLib::maxFields($this->planet),
                'planetMinTemp' => $this->planet['planet_temp_min'],
                'planetMaxTemp' => $this->planet['planet_temp_max'],
                'galaxyGalaxy' => $this->planet['planet_galaxy'],
                'galaxySystem' => $this->planet['planet_system'],
                'galaxyPlanet' => $this->planet['planet_planet'],
                'userRank' => $this->getUserRank(),
            ]
        );
    }

    private function getCurrentWork(array $user_planet, $is_current_planet = true): string
    {
        // THE PLANET IS "FREE" BY DEFAULT
        $building_block = $this->langs->line('ov_free');

        if (!$is_current_planet) {
            // UPDATE THE PLANET INFORMATION FIRST, MAY BE SOMETHING HAS JUST FINISHED
            UpdatesLibrary::updateBuildingsQueue($user_planet, $this->user);
        }

        if ($user_planet['planet_b_building'] != 0) {
            if ($user_planet['planet_b_building'] != 0) {
                $queue = explode(';', $user_planet['planet_b_building_id']); // GET ALL
                $current_building = explode(',', $queue[0]); // GET ONLY THE FIRST ELEMENT
                $building = $current_building[0]; // THE BUILDING
                $level = $current_building[1]; // THE LEVEL
                $time_to_end = $current_building[3] - time(); // THE TIME

                // THE BUILDING BLOCK
                if ($is_current_planet) {
                    $building_block = DevelopmentsLib::currentBuilding("overview", $this->langs->language, $building);
                    $building_block .= $this->langs->language[$this->objects->getObjects($building)] . ' (' . $level . ')';
                    $building_block .= "<br /><div id=\"blc\" class=\"z\">" . FormatLib::prettyTime($time_to_end) . "</div>";
                    $building_block .= "\n<script language=\"JavaScript\">";
                    $building_block .= "\n	pp = \"" . $time_to_end . "\";\n";
                    $building_block .= "\n	pk = \"" . 1 . "\";\n";
                    $building_block .= "\n	pm = \"cancel\";\n";
                    $building_block .= "\n	pl = \"" . $this->planet['planet_id'] . "\";\n";
                    $building_block .= "\n	t();\n";
                    $building_block .= "\n</script>\n";
                } else {
                    $building_block = '' . $this->langs->language[$this->objects->getObjects($building)] . ' (' . $level . ')';
                    $building_block .= '<br><font color="#7f7f7f">(' . FormatLib::prettyTime($time_to_end) . ')</font>';
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
                $new_message .= '<th role="cell" colspan="4">' . UrlHelper::setUrl('game.php?page=messages', $this->langs->line('ov_have_new_message'), $this->langs->line('ov_have_new_message')) . '</th>';
            }

            if ($this->user['new_message'] > 1) {
                $link_text = str_replace('%m', FormatLib::prettyNumber($this->user['new_message']), $this->langs->line('ov_have_new_messages'));
                $new_message .= '<th role="cell" colspan="4">' . UrlHelper::setUrl('game.php?page=messages', $link_text, $link_text) . '</th>';
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

        $own_fleets = $this->overviewModel->getOwnFleets($this->user['user_id']);

        foreach ($own_fleets as $fleets) {
            ######################################
            #
            # own fleets
            #
            ######################################

            $start_time = $fleets['fleet_start_time'];
            $stay_time = $fleets['fleet_end_stay'];
            $end_time = $fleets['fleet_end_time'];

            $fleet_status = $fleets['fleet_mess'];
            $fleet_group = $fleets['fleet_group'];
            $id = $fleets['fleet_id'];

            if ($fleets['fleet_owner'] == $this->user['user_id']) {
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

            ######################################
            #
            # incoming fleets
            #
            ######################################
            if ($fleets['fleet_owner'] != $this->user['user_id']) {
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

            ######################################
            #
            # other fleets
            #
            ######################################

            if ($fleets['fleet_owner'] != $this->user['user_id']) {
                $acs_member = false;

                if (in_array($this->user['user_id'], explode(',', $fleets['acs_members'] ?? ''))) {
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
        $return['moon_img'] = '';
        $return['moon'] = '';

        if ($this->planet['moon_id'] != 0 && $this->planet['moon_destroyed'] == 0 && $this->planet['planet_type'] == PlanetTypesEnumerator::PLANET) {
            $moon_name = $this->planet['moon_name'] . " (" . $this->langs->line('moon') . ")";
            $url = 'game.php?page=overview&cp=' . $this->planet['moon_id'] . '&re=0';
            $image = asset('upload/skins/xgproyect/planets/' . $this->planet['moon_image'] . '.jpg');
            $attributes = 'height="50" width="50"';

            $return['moon_img'] = UrlHelper::setUrl($url, Functions::setImage($image, $moon_name, $attributes), $moon_name);
            $return['moon'] = $moon_name;
        }

        return $return;
    }

    private function getPlanets(): string
    {
        $colony = 1;

        $planets_query = $this->overviewModel->getPlanets($this->user['user_id']);
        $planet_block = '<tr>';

        foreach ($planets_query as $user_planet) {
            if ($user_planet['planet_id'] != $this->user['user_current_planet'] && $user_planet['planet_type'] != PlanetTypesEnumerator::MOON) {
                $url = 'game.php?page=overview&cp=' . $user_planet['planet_id'] . '&re=0';
                $image = asset('upload/skins/xgproyect/planets/small/s_' . $user_planet['planet_image'] . '.jpg');
                $attributes = 'height="50" width="50"';

                $planet_block .= '<th>' . $user_planet['planet_name'] . '<br>';
                $planet_block .= UrlHelper::setUrl($url, Functions::setImage($image, $user_planet['planet_name'], $user_planet['planet_name'], $attributes));
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
        $user_rank = '-';
        $total_rank = $this->user['user_statistic_total_rank'] == '' ? $this->planet['stats_users'] : $this->user['user_statistic_total_rank'];

        if ($this->noob->isRankVisible($this->user['user_authlevel'])) {
            $user_rank = FormatLib::prettyNumber($this->user['user_statistic_total_points']) . " (" . $this->langs->line('ov_place') . ' ' . UrlHelper::setUrl('game.php?page=statistics&range=' . $total_rank, $total_rank, $total_rank) . ' ' . $this->langs->line('ov_of') . ' ' . $this->planet['stats_users'] . ")";
        }

        return $user_rank;
    }
}
