<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\Game\Formulas\FleetsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use Illuminate\Routing\Controller as BaseController;
use App\Services\SettingsService;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\GalaxyLib;
use Xgp\App\Libraries\NoobsProtectionLib;
use Xgp\App\Libraries\Users;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class GalaxyController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];
    private array $galaxy = [];
    private int $planet_count = 0;
    private $_resource;
    private $_pricelist;
    private $_reslist;
    private $_galaxy;
    private $_system;
    private $noob;
    private $_galaxyLib;
    public function __construct(
        private FormatService $formatService,
        private FleetsService $fleetsService,
        private OfficerService $officerService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Galaxy));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->_resource = Objects::getInstance()->getObjects();
        $this->_pricelist = Objects::getInstance()->getPrice();
        $this->_reslist = Objects::getInstance()->getObjectsList();
        $this->noob = new NoobsProtectionLib();
        $this->_galaxyLib = new GalaxyLib();

        if ($this->user['preference_vacation_mode'] > 0) {
            Functions::message(__('game/galaxy.gl_no_access_vm_on'), '', '');
        }

        $this->runAction();
        $this->buildPage();
    }

    private function runAction(): void
    {
        if (isset($_GET['fleet']) && $_GET['fleet'] == 'true') {
            $this->sendFleet();
        }

        if (isset($_GET['missiles']) && $_GET['missiles'] == 'true') {
            $this->sendMissiles();
        }
    }

    private function buildPage(): void
    {
        // fleets
        $max_fleets = $this->fleetsService->getMaxFleets(
            (int) $this->user['research_computer_technology'],
            $this->officerService->isOfficerActive((int) $this->user['premium_officier_admiral'], time())
        );
        $fleetCountRow = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(`fleet_id`) AS total_fleets
                FROM `' . FLEETS . "`
                WHERE `fleet_owner` = '" . (int) $this->user['id'] . "';"
            )
        );
        $current_fleets = $fleetCountRow !== null ? (int) $fleetCountRow->total_fleets : 0;

        // missiles and espionage probes
        $CurrentPlID = $this->planet['planet_id'];
        $CurrentSP = $this->planet['ship_espionage_probe'];

        if (isset($_GET['mode'])) {
            $mode = intval($_GET['mode']);
        } else {
            $mode = 0;
        }

        $setted_position = $this->validatePosition($mode);
        $this->_galaxy = (int) $setted_position['galaxy'];
        $this->_system = (int) $setted_position['system'];
        $planet = $setted_position['planet'];

        if ($mode == 2 && $this->planet['defense_interplanetary_missile'] < 1) {
            Functions::message(__('game/galaxy.gl_no_missiles'), 'game.php?page=galaxy&mode=0', 2);
            exit;
        }

        $this->galaxy = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    "SELECT
                        (
                            SELECT
                                CONCAT (GROUP_CONCAT(`buddy_receiver`), ',', GROUP_CONCAT(`buddy_sender`)) AS `buddys`
                            FROM `" . BUDDY . '` AS b
                            WHERE
                            (
                                b.`buddy_receiver` = ' . (int) $this->user['id'] . '
                                OR
                                b.`buddy_sender` = ' . (int) $this->user['id'] . '
                            )
                        ) AS buddys,
                        p.`planet_debris_metal` AS `metal`,
                        p.`planet_debris_crystal` AS `crystal`,
                        p.`planet_id` AS `id_planet`,
                        p.`planet_galaxy`,
                        p.`planet_system`,
                        p.`planet_planet`,
                        p.`planet_type`,
                        p.`planet_destroyed`,
                        p.`planet_name`,
                        p.`planet_image`,
                        p.`planet_last_update`,
                        p.`planet_user_id`,
                        u.`id`,
                        u.`ally_id`,
                        b.`user_id` AS `banned`,
                        pr.`preference_vacation_mode`,
                        u.`onlinetime`,
                        u.`name`,
                        u.`authlevel`,
                        s.`user_statistic_total_rank`,
                        s.`user_statistic_total_points`,
                        m.`planet_id` AS `id_luna`,
                        m.`planet_diameter`,
                        m.`planet_temp_min`,
                        m.`planet_destroyed` AS `destroyed_moon`,
                        m.`planet_name` AS `name_moon`,
                        a.`alliance_name`,
                        a.`alliance_tag`,
                        a.`alliance_web`,
                        (
                            SELECT
                                COUNT(`id`) AS `ally_members`
                            FROM `' . USERS . '`
                            WHERE `ally_id` = a.`alliance_id`
                        ) AS `ally_members`
                    FROM `' . PLANETS . '` AS p
                        INNER JOIN `' . USERS . '` AS u
                            ON p.`planet_user_id` = u.`id`
                        INNER JOIN `' . PREFERENCES . '` AS pr
                            ON pr.`preference_user_id` = u.`id`
                        INNER JOIN `' . USERS_STATISTICS . '` AS s
                            ON s.`user_statistic_user_id` = u.`id`
                        LEFT JOIN `' . ALLIANCE . '` AS a
                            ON a.`alliance_id` = u.`ally_id`
                        LEFT JOIN `' . PLANETS . '` AS m
                            ON m.`planet_id` = (
                                SELECT mp.`planet_id`
                                FROM `' . PLANETS . '` AS mp
                                WHERE (
                                    mp.`planet_galaxy` = p.`planet_galaxy`
                                    AND
                                    mp.`planet_system` = p.`planet_system`
                                    AND
                                    mp.`planet_planet` = p.`planet_planet`
                                    AND
                                    mp.`planet_type` = "3"
                                )
                            )
                        LEFT JOIN `' . BANNED . "` AS b
                            ON b.`user_id` = u.`id`
                    WHERE (
                            p.planet_galaxy = '" . $this->_galaxy . "'
                            AND
                            p.planet_system = '" . $this->_system . "'
                            AND
                            p.planet_type = '1'
                            AND
                            (
                                p.planet_planet > '0'
                                AND
                                p.planet_planet <= '" . MAX_PLANET_IN_SYSTEM . "'
                            )
                    )
                    ORDER BY p.planet_planet;"
                )
            )
        );

        $parse['selected_galaxy'] = $this->_galaxy;
        $parse['selected_system'] = $this->_system;
        $parse['selected_planet'] = $planet;
        $parse['currentmip'] = $this->planet['defense_interplanetary_missile'];
        $parse['maxfleetcount'] = $current_fleets;
        $parse['fleetmax'] = $max_fleets;
        $parse['recyclers'] = $this->formatService->prettyNumber((int) $this->planet['ship_recycler']);
        $parse['spyprobes'] = $this->formatService->prettyNumber((int) $CurrentSP);
        $parse['missile_count'] = sprintf(__('game/galaxy.gl_missil_to_launch'), $this->planet['defense_interplanetary_missile']);
        $parse['current'] = isset($_GET['current']) ? $_GET['current'] : null;
        $parse['current_galaxy'] = $this->planet['planet_galaxy'];
        $parse['current_system'] = $this->planet['planet_system'];
        $parse['current_planet'] = $this->planet['planet_planet'];
        $parse['coords'] = $this->formatService->prettyCoords((int)$this->_galaxy, (int)$this->_system, (int) $planet);
        $parse['planet_type'] = $this->planet['planet_type'];
        $parse['mip'] = ($mode == 2) ? Template::render(
            'galaxy/galaxy_missile_selector',
            $parse
        ) : ' ';

        Template::legacyView(
            'galaxy.galaxy_view',
            array_merge(
                [
                    'list_of_positions' => $this->buildPositionsList(),
                    'planet_count' => $this->planet_count,
                    'max_galaxy' => MAX_GALAXY_IN_WORLD,
                    'max_system' => MAX_SYSTEM_IN_GALAXY,
                ],
                $parse
            )
        );
    }

    /**
     * Build the list of positions for the galaxy
     *
     * @return array
     */
    private function buildPositionsList(): array
    {
        $list_of_positions = [];
        $galaxy_row = new $this->_galaxyLib(
            $this->user,
            $this->planet,
            $this->_galaxy,
            $this->_system,
        );

        // set the current planets
        foreach ($this->galaxy as $planet) {
            $this->planet_count++;

            $list_of_positions[$planet['planet_planet']] = $galaxy_row->buildRow($planet, $planet['planet_planet']);
        }

        // fill the empty positions
        for ($i = 1; $i <= MAX_PLANET_IN_SYSTEM; $i++) {
            if (!isset($list_of_positions[$i])) {
                $list_of_positions[$i] = [
                    'pos' => $i,
                    'planet' => '',
                    'planetname' => '',
                    'moon' => '',
                    'debris' => '',
                    'username' => '',
                    'alliance' => '',
                    'actions' => '',
                ];
            }
        }

        ksort($list_of_positions);

        return $list_of_positions;
    }

    /**
     * method validate_position
     * param $mode
     * return validates the position setted by the user
     */
    private function validatePosition($mode)
    {
        $return['galaxy'] = '';
        $return['system'] = '';
        $return['planet'] = '';

        switch ($mode) {
            case 0:
                $galaxy = $this->planet['planet_galaxy'];
                $system = $this->planet['planet_system'];
                $planet = $this->planet['planet_planet'];
                break;
            case 1:
                // validate, we want only numbers
                $galaxy = (isset($_POST['galaxy']) && intval($_POST['galaxy'])) ? preg_replace('[^0-9]', '', $_POST['galaxy']) : 1;
                $system = (isset($_POST['system']) && intval($_POST['system'])) ? preg_replace('[^0-9]', '', $_POST['system']) : 1;

                /**
                 * Change galaxy
                 */
                if (isset($_POST['galaxyRight'])) {
                    if ($galaxy >= MAX_GALAXY_IN_WORLD) {
                        $galaxy = 1;
                    } else {
                        $galaxy++;
                    }
                }

                if (isset($_POST['galaxyLeft'])) {
                    if ($galaxy <= 1) {
                        $galaxy = MAX_GALAXY_IN_WORLD;
                    } else {
                        $galaxy--;
                    }
                }

                /**
                 * Change system
                 */
                if (isset($_POST['systemRight'])) {
                    if ($system >= MAX_SYSTEM_IN_GALAXY) {
                        $system = 1;
                    } else {
                        $system++;
                    }
                }

                if (isset($_POST['systemLeft'])) {
                    if ($system <= 1) {
                        $system = MAX_SYSTEM_IN_GALAXY;
                    } else {
                        $system--;
                    }
                }
                break;
            case 2:
                $galaxy = intval($_GET['galaxy']);
                $system = intval($_GET['system']);
                $planet = intval($_GET['planet']);
                break;
            case 3:
                $galaxy = intval($_GET['galaxy']);
                $system = intval($_GET['system']);
                break;
            default:
                $galaxy = 1;
                $system = 1;
                break;
        }

        $return['galaxy'] = $galaxy;
        $return['system'] = $system;
        $return['planet'] = isset($planet) ? $planet : null;

        return $return;
    }

    /**
     * method send_missiles
     * param
     * return send missiles routine
     */
    private function sendMissiles()
    {
        $galaxy = intval($_GET['galaxy']);
        $system = intval($_GET['system']);
        $planet = intval($_GET['planet']);
        $missiles_amount = ($_POST['SendMI'] < 0) ? 0 : intval($_POST['SendMI']);
        $target = $_POST['Target'];

        $current_missiles = $this->planet['defense_interplanetary_missile'];
        $tempvar1 = abs($system - $this->planet['planet_system']);
        $tempvar2 = Formulas::missileRange((int) $this->user['research_impulse_drive']);

        $targetRow = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    u.`id`,
                    u.`onlinetime`,
                    u.`authlevel`,
                    pr.`preference_vacation_mode`
                FROM `' . USERS . '` AS u
                INNER JOIN `' . PREFERENCES . '` AS pr ON pr.preference_user_id = u.id
                WHERE u.id = (
                    SELECT `planet_user_id`
                    FROM `' . PLANETS . '`
                    WHERE planet_galaxy = ' . $galaxy . '  AND
                        planet_system = ' . $system . ' AND
                        planet_planet = ' . $planet . ' AND
                        planet_type = 1
                    LIMIT 1
                    )
                LIMIT 1'
            )
        );
        $target_user = $targetRow !== null ? (array) $targetRow : null;

        $user_points = $this->noob->returnPoints($this->user['id'], $target_user['id']);
        $MyGameLevel = $user_points['user_points'];
        $HeGameLevel = $user_points['target_points'];

        $error = '';
        $errors = 0;

        if ($this->planet['building_missile_silo'] < 4) {
            $error .= __('game/galaxy.gl_silo_level') . '<br>';
            $errors++;
        }

        if ($this->user['research_impulse_drive'] == 0) {
            $error .= __('game/galaxy.gl_impulse_drive_required') . '<br>';
            $errors++;
        }

        if ($tempvar1 >= $tempvar2 || $galaxy != $this->planet['planet_galaxy']) {
            $error .= __('game/galaxy.gl_not_send_other_galaxy') . '<br>';
            $errors++;
        }

        if (!$target_user) {
            $error .= __('game/galaxy.gl_planet_doesnt_exists') . '<br>';
            $errors++;
        }

        if ($missiles_amount > $current_missiles) {
            $error .= __('game/galaxy.gl_cant_send') . $missiles_amount . __('game/galaxy.gl_missile') . $current_missiles . '<br>';
            $errors++;
        }

        if (((!is_numeric($target) && $target != 'all') or ($target < 0 or $target > 8))) {
            $error .= __('game/galaxy.gl_wrong_target') . '<br>';
            $errors++;
        }

        if ($current_missiles == 0) {
            $error .= __('game/galaxy.gl_no_missiles') . '<br>';
            $errors++;
        }

        if ($missiles_amount == 0) {
            $error .= __('game/galaxy.gl_add_missile_number') . '<br>';
            $errors++;
        }

        if ($target_user['onlinetime'] >= (time() - 60 * 60 * 24 * 7)) {
            if ($this->noob->isWeak(intval($MyGameLevel), intval($HeGameLevel))) {
                $error .= __('game/fleet.fl_week_player') . '<br>';
                $errors++;
            } elseif ($this->noob->isStrong(intval($MyGameLevel), intval($HeGameLevel))) {
                $error .= __('game/fleet.fl_strong_player') . '<br>';
                $errors++;
            }
        }
        if ($target_user['preference_vacation_mode'] > 0) {
            $error .= __('game/fleet.fl_in_vacation_player') . '<br>';
            $errors++;
        }

        if ($errors != 0) {
            Functions::message($error, 'game.php?page=galaxy&mode=0&galaxy=' . $galaxy . '&system=' . $system, 3);
        }

        $flight_time = round(((30 + (60 * $tempvar1)) * 2500) / app(SettingsService::class)->getInt('fleet_speed'));

        $DefenseLabel = [
            0 => __('game/galaxy.gl_all_defenses'),
            1 => __('game/defenses.defense_rocket_launcher'),
            2 => __('game/defenses.defense_light_laser'),
            3 => __('game/defenses.defense_heavy_laser'),
            4 => __('game/defenses.defense_gauss_cannon'),
            5 => __('game/defenses.defense_ion_cannon'),
            6 => __('game/defenses.defense_plasma_turret'),
            7 => __('game/defenses.defense_small_shield_dome'),
            8 => __('game/defenses.defense_large_shield_dome'),
        ];

        $missileData = [
            'fleet_owner' => $this->user['id'],
            'fleet_amount' => $missiles_amount,
            'fleet_array' => FleetsLib::setFleetShipsArray([503 => $missiles_amount]),
            'fleet_start_time' => (time() + $flight_time),
            'fleet_start_galaxy' => $this->planet['planet_galaxy'],
            'fleet_start_system' => $this->planet['planet_system'],
            'fleet_start_planet' => $this->planet['planet_planet'],
            'fleet_end_time' => (time() + $flight_time + 1),
            'fleet_end_galaxy' => $galaxy,
            'fleet_end_system' => $system,
            'fleet_end_planet' => $planet,
            'fleet_target_obj' => $target,
            'fleet_target_owner' => $target_user['id'],
            'current_planet' => $this->user['current_planet'],
        ];

        DB::transaction(function () use ($missileData): void {
            DB::statement(
                $this->prepareSql(
                    'INSERT INTO `' . FLEETS . "` SET
                    `fleet_owner` = '" . $missileData['fleet_owner'] . "',
                    `fleet_mission` = '10',
                    `fleet_amount` = " . $missileData['fleet_amount'] . ",
                    `fleet_array` = '" . $missileData['fleet_array'] . "',
                    `fleet_start_time` = '" . $missileData['fleet_start_time'] . "',
                    `fleet_start_galaxy` = '" . $missileData['fleet_start_galaxy'] . "',
                    `fleet_start_system` = '" . $missileData['fleet_start_system'] . "',
                    `fleet_start_planet` ='" . $missileData['fleet_start_planet'] . "',
                    `fleet_start_type` = '1',
                    `fleet_end_time` = '" . $missileData['fleet_end_time'] . "',
                    `fleet_end_stay` = '0',
                    `fleet_end_galaxy` = '" . $missileData['fleet_end_galaxy'] . "',
                    `fleet_end_system` = '" . $missileData['fleet_end_system'] . "',
                    `fleet_end_planet` = '" . $missileData['fleet_end_planet'] . "',
                    `fleet_end_type` = '1',
                    `fleet_target_obj` = '" . $missileData['fleet_target_obj'] . "',
                    `fleet_resource_metal` = '0',
                    `fleet_resource_crystal` = '0',
                    `fleet_resource_deuterium` = '0',
                    `fleet_target_owner` = '" . $missileData['fleet_target_owner'] . "',
                    `fleet_group` = '0',
                    `fleet_mess` = '0',
                    `fleet_creation` = '" . time() . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . DEFENSES . '` SET
                        `defense_interplanetary_missile` = `defense_interplanetary_missile` - ' . $missileData['fleet_amount'] . "
                    WHERE `defense_planet_id` =  '" . $missileData['current_planet'] . "'"
                )
            );
        });

        Functions::message('<b>' . $missiles_amount . '</b>' . __('game/galaxy.gl_missiles_sended') . $DefenseLabel[$target], 'game.php?page=overview', 3);
    }

    /**
     * method send_fleet
     * param
     * return send fleet routine
     */
    private function sendFleet()
    {
        $max_spy_probes = $this->user['preference_spy_probes'];
        $UserSpyProbes = $this->planet['ship_espionage_probe'];
        $UserRecycles = $this->planet['ship_recycler'];
        $UserDeuterium = $this->planet['planet_deuterium'];
        $UserMissiles = $this->planet['defense_interplanetary_missile'];
        $fleet = [];
        $speedalls = [];
        $PartialFleet = false;
        $PartialCount = 0;
        $order = isset($_POST['order']) ? $_POST['order'] : null;
        $ResultMessage = '';
        $fleet['fleetlist'] = '';
        $fleet['amount'] = '';

        switch ($order) {
            case 6:
                $_POST['ship210'] = $_POST['shipcount'];
                break;
            case 7:
                $_POST['ship208'] = $_POST['shipcount'];
                break;
            case 8:
                $_POST['ship209'] = $_POST['shipcount'];
                break;
        }

        $fleet['amount'] = 0;

        foreach ($this->_reslist['fleet'] as $ship_id) {
            $TName = 'ship' . $ship_id;
            $ship_amount = isset($_POST[$TName]) ? (int) $_POST[$TName] : 0;

            if ($ship_id > 200 && $ship_id < 300 && $ship_amount > 0) {
                if ($ship_amount > $this->planet[$this->_resource[$ship_id]]) {
                    $fleet['fleetarray'][$ship_id] = (int) $this->planet[$this->_resource[$ship_id]];
                    $fleet['fleetlist'] .= $ship_id . ',' . $this->planet[$this->_resource[$ship_id]] . ';';
                    $fleet['amount'] += (int) $this->planet[$this->_resource[$ship_id]];
                    $PartialCount += (int) $this->planet[$this->_resource[$ship_id]];

                    // we sent less that the amount requested
                    $PartialFleet = true;
                } else {
                    $fleet['fleetarray'][$ship_id] = $ship_amount;
                    $fleet['fleetlist'] .= $ship_id . ',' . $ship_amount . ';';
                    $fleet['amount'] += $ship_amount;
                    $speedalls[$ship_id] = $ship_amount;
                }
            }
        }

        $errors_types = [
            600 => __('game/galaxy.gl_success'),
            601 => __('game/galaxy.gl_error'),
            602 => __('game/galaxy.gl_no_moon'),
            603 => __('game/galaxy.gl_noob_protection'),
            604 => __('game/galaxy.gl_too_strong'),
            605 => __('game/galaxy.gl_vacation_mode'),
            610 => __('game/galaxy.gl_only_amount_ships'),
            611 => __('game/galaxy.gl_no_ships'),
            612 => __('game/galaxy.gl_no_slots'),
            613 => __('game/galaxy.gl_no_deuterium'),
            614 => __('game/galaxy.gl_no_planet'),
            615 => __('game/galaxy.gl_not_enough_storage'),
            616 => __('game/galaxy.gl_multi_alarm'),
        ];

        if ($PartialFleet == true) {
            if ($PartialCount < 1) {
                die('611 ');
            }
        }

        $galaxy = isset($_POST['galaxy']) ? (int) $_POST['galaxy'] : 0;
        $system = isset($_POST['system']) ? (int) $_POST['system'] : 0;
        $planet = isset($_POST['planet']) ? (int) $_POST['planet'] : 0;
        $FleetArray = isset($fleet['fleetarray']) ? $fleet['fleetarray'] : null;

        if (($galaxy > MAX_GALAXY_IN_WORLD or $galaxy < 1) or ($system > MAX_SYSTEM_IN_GALAXY or $system < 1) or ($planet > MAX_PLANET_IN_SYSTEM or $planet < 1) or (is_null($FleetArray))) {
            die('614 ');
        }

        $fleetCountRow2 = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(`fleet_id`) AS total_fleets
                FROM `' . FLEETS . "`
                WHERE `fleet_owner` = '" . (int) $this->user['id'] . "';"
            )
        );
        $current_fleets = $fleetCountRow2 !== null ? (int) $fleetCountRow2->total_fleets : 0;

        $targetRow2 = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    u.`id`,
                    u.`onlinetime`,
                    u.`authlevel`,
                    pr.`preference_vacation_mode`
                FROM `' . USERS . '` AS u
                INNER JOIN `' . PREFERENCES . '` AS pr ON pr.preference_user_id = u.id
                WHERE u.id = (
                    SELECT `planet_user_id`
                    FROM `' . PLANETS . '`
                    WHERE planet_galaxy = ' . $galaxy . '  AND
                        planet_system = ' . $system . ' AND
                        planet_planet = ' . $planet . ' AND
                        planet_type = ' . (int) $_POST['planettype'] . '
                    LIMIT 1
                    )
                LIMIT 1'
            )
        );
        $target_user = $targetRow2 !== null ? (array) $targetRow2 : null;

        if ($target_user == null) {
            $target_user = $this->user;
        }

        // invisible debris by jstar
        if ($order == 8) {
            $debrisRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        `planet_invisible_start_time`,
                        `planet_debris_metal`,
                        `planet_debris_crystal`
                    FROM `' . PLANETS . "`
                    WHERE `planet_galaxy` = '" . $galaxy . "'
                        AND `planet_system` = '" . $system . "'
                        AND `planet_planet` = '" . $planet . "'
                        AND `planet_type` = 1;"
                )
            );
            $TargetGPlanet = $debrisRow !== null ? (array) $debrisRow : null;

            if ($TargetGPlanet['planet_debris_metal'] == 0 && $TargetGPlanet['planet_debris_crystal'] == 0 && time() > ($TargetGPlanet['planet_invisible_start_time'] + DEBRIS_LIFE_TIME)) {
                die();
            }
        }

        $user_points = $this->noob->returnPoints($this->user['id'], $target_user['id']);
        $CurrentPoints = $user_points['user_points'];
        $TargetPoints = $user_points['target_points'];
        $TargetVacat = $target_user['preference_vacation_mode'];

        if (($this->fleetsService->getMaxFleets((int) $this->user[$this->_resource[108]], $this->officerService->isOfficerActive((int) $this->user['premium_officier_admiral'], time()))) <= $current_fleets) {
            die('612 ');
        }

        if (!is_array($FleetArray)) {
            die('611 ');
        }

        if (!(($order == 6) or ($order == 8))) {
            die('601 ');
        }

        if (($TargetVacat && $order != 8) or ($this->user['preference_vacation_mode'] > 0)) {
            die('605 ');
        }

        if ($target_user['onlinetime'] >= (time() - 60 * 60 * 24 * 7)) {
            if ($this->noob->isWeak(intval($CurrentPoints), intval($TargetPoints)) && $target_user['id'] != '' && $order == 6) {
                die('603 ');
            }

            if ($this->noob->isStrong(intval($CurrentPoints), intval($TargetPoints)) && $target_user['id'] != '' && $order == 6) {
                die('604 ');
            }
        }

        if ($target_user['id'] == '' && $order != 8) {
            die('601 ');
        }

        if (($target_user['id'] == $this->planet['planet_user_id']) && ($order == 6)) {
            die('601 ');
        }

        $Distance = $this->fleetsService->targetDistance((int) $this->planet['planet_galaxy'], (int) $_POST['galaxy'], (int) $this->planet['planet_system'], (int) $_POST['system'], (int) $this->planet['planet_planet'], (int) $_POST['planet']);
        $speedall = $this->fleetsService->fleetMaxSpeed($FleetArray, (int) $this->user['research_combustion_drive'], (int) $this->user['research_impulse_drive'], (int) $this->user['research_hyperspace_drive']);
        $SpeedAllMin = min($speedall);
        $Duration = $this->fleetsService->missionDuration(10, (int) $SpeedAllMin, $Distance, Functions::fleetSpeedFactor());

        $fleet['fly_time'] = $Duration;
        $fleet['start_time'] = $Duration + time();
        $fleet['end_time'] = ($Duration * 2) + time();

        $FleetShipCount = 0;
        $FleetDBArray = [];
        $fleet_sub_query = [];
        $consumption = 0;
        $SpeedFactor = Functions::fleetSpeedFactor();

        foreach ($FleetArray as $Ship => $Count) {
            if ($Ship != '') {
                $ShipSpeed = $this->_pricelist[$Ship]['speed'];
                $spd = 35000 / ($Duration * $SpeedFactor - 10) * sqrt($Distance * 10 / $ShipSpeed);
                $basicConsumption = $this->_pricelist[$Ship]['consumption'] * $Count;
                $consumption += $basicConsumption * $Distance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
                $FleetShipCount += $Count;
                $FleetDBArray[$Ship] = $Count;
                $fleet_sub_query[$this->_resource[$Ship]] = $Count;
            }
        }

        $consumption = round($consumption) + 1;

        if ($UserDeuterium < $consumption) {
            die('613 ');
        }

        if (app(SettingsService::class)->getInt('adm_attack') == 1 && $target_user['authlevel'] > 0) {
            die('601 ');
        }

        $fleetData = [
            'fleet_owner' => $this->user['id'],
            'fleet_mission' => intval($order),
            'fleet_amount' => $FleetShipCount,
            'fleet_array' => FleetsLib::setFleetShipsArray($FleetDBArray),
            'fleet_start_time' => $fleet['start_time'],
            'fleet_start_galaxy' => $this->planet['planet_galaxy'],
            'fleet_start_system' => $this->planet['planet_system'],
            'fleet_start_planet' => $this->planet['planet_planet'],
            'fleet_start_type' => $this->planet['planet_type'],
            'fleet_end_time' => $fleet['end_time'],
            'fleet_end_galaxy' => intval($_POST['galaxy']),
            'fleet_end_system' => intval($_POST['system']),
            'fleet_end_planet' => intval($_POST['planet']),
            'fleet_end_type' => intval($_POST['planettype']),
            'fleet_resource_metal' => 0,
            'fleet_resource_crystal' => 0,
            'fleet_resource_deuterium' => 0,
            'fleet_fuel' => $consumption,
            'fleet_target_owner' => $target_user['id'],
        ];

        DB::transaction(function () use ($fleetData, $fleet_sub_query): void {
            $sql = [];

            foreach ($fleetData as $field => $value) {
                $sql[] = '`' . $field . "` = '" . $value . "'";
            }

            DB::statement(
                $this->prepareSql(
                    'INSERT INTO `' . FLEETS . '` SET '
                    . join(', ', $sql) .
                    ", `fleet_creation` = '" . time() . "';"
                )
            );

            $shipSql = [];

            foreach ($fleet_sub_query as $field => $value) {
                $shipSql[] = '`' . $field . '` = `' . $field . "` - '" . $value . "'";
            }

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '` AS p
                    INNER JOIN `' . SHIPS . '` AS s ON s.`ship_planet_id` = p.`planet_id` SET
                    ' . join(', ', $shipSql) . ',
                    `planet_metal` = `planet_metal` - ' . $fleetData['fleet_resource_metal'] . ',
                    `planet_crystal` = `planet_crystal` - ' . $fleetData['fleet_resource_crystal'] . ',
                    `planet_deuterium` = `planet_deuterium` - ' . ($fleetData['fleet_resource_deuterium'] + $fleetData['fleet_fuel']) . '
                    WHERE `planet_id` = ' . $this->planet['planet_id'] . ';'
                )
            );
        });

        foreach ($FleetArray as $Ships => $Count) {
            if ($max_spy_probes > $this->planet[$this->_resource[$Ships]]) {
                $ResultMessage = '610 ' . $FleetShipCount;
            }
        }

        if ($ResultMessage == '') {
            $ResultMessage = '600 ' . $Ships;
        }

        die($ResultMessage);
    }
}
