<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\Formulas\FleetsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\MissionsEnumerator as Missions;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator as PlanetTypes;
use Xgp\App\Core\Enumerators\ShipsEnumerator as Ships;
use Xgp\App\Core\Objects;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\Fleets;
use Xgp\App\Libraries\NoobsProtectionLib;
use Xgp\App\Libraries\Premium\Premium;
use Xgp\App\Libraries\Research\Researches;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Fleet4Controller extends BaseController
{
    use PreparesLegacySql;
    public const REDIRECT_TARGET = 'game.php?page=movement';

    private array $user = [];
    private array $planet = [];
    private ?Fleets $_fleets = null;
    private ?Researches $_research = null;
    private ?Premium $_premium = null;
    private array $_clean_input_data = [];
    private array $_fleet_data = [
        'fleet_owner' => 0,
        'fleet_mission' => 0,
        'fleet_amount' => 0,
        'fleet_array' => '',
        'fleet_start_time' => 0,
        'fleet_start_galaxy' => 0,
        'fleet_start_system' => 0,
        'fleet_start_planet' => 0,
        'fleet_start_type' => 0,
        'fleet_end_time' => 0,
        'fleet_end_stay' => 0,
        'fleet_end_galaxy' => 0,
        'fleet_end_system' => 0,
        'fleet_end_planet' => 0,
        'fleet_end_type' => 0,
        'fleet_resource_metal' => 0,
        'fleet_resource_crystal' => 0,
        'fleet_resource_deuterium' => 0,
        'fleet_fuel' => 0,
        'fleet_target_owner' => 0,
        'fleet_group' => 0,
    ];
    private array $_target_data = [];
    private bool $_own_planet = false;
    private bool $_occupied_planet = false;
    private int $_fleet_storage = 0;
    private array $_fleet_ships = [];
    private Users $userLibrary;
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private FleetsService $fleetsService,
        private OfficerService $officerService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Fleet));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->userLibrary = new Users();
        $this->objects = new Objects();

        $this->setUpFleets();
        $this->buildPage();
    }

    private function setUpFleets(): void
    {
        $userId = (int) $this->user['id'];
        $this->_fleets = new Fleets(
            $userId > 0 ? array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT f.*
                        FROM `' . FLEETS . "` f
                        WHERE f.`fleet_owner` = '" . $userId . "';"
                    )
                )
            ) : [],
            $userId
        );

        $this->_research = new Researches(
            [$this->user],
            (int) $this->user['id']
        );

        $this->_premium = new Premium(
            [$this->user],
            (int) $this->user['id']
        );
    }

    private function buildPage(): void
    {
        // filter stuff from fleet1, fleet2 and fleet3
        $this->setInputsData();

        // get the target
        $this->getTarget();

        // validate all the received data
        if ($this->runValidations()) {
            // final step, send and redirect
            $this->sendFleet();
        }

        Functions::redirect(self::REDIRECT_TARGET);
    }

    private function setInputsData(): void
    {
        $exp_time = $this->_research->getCurrentResearch()->getResearchAstrophysics();

        $min_exp_time = $exp_time <= 0 ? 0 : 1;
        $max_exp_time = $exp_time;

        $data = filter_input_array(INPUT_POST, [
            'mission' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 15],
            ],
            'resource1' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0, 'max_range' => $this->planet['planet_metal']],
            ],
            'resource2' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0, 'max_range' => $this->planet['planet_crystal']],
            ],
            'resource3' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0, 'max_range' => $this->planet['planet_deuterium']],
            ],
            'expeditiontime' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => $min_exp_time, 'max_range' => $max_exp_time],
            ],
            'holdingtime' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0, 'max_range' => 32],
            ],
        ]);

        if (is_null($data)) {
            Functions::redirect('game.php?page=fleet1');
        }

        $this->_clean_input_data = $data;
    }

    private function getTarget(): void
    {
        $target_data = $this->getTargetData();

        $targetType = (int) ($target_data['type'] != 2 ? $target_data['type'] : 1);
        $targetRow = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    p.`planet_user_id`,
                    p.`planet_debris_metal`,
                    p.`planet_debris_crystal`,
                    p.`planet_invisible_start_time`,
                    p.`planet_destroyed`,
                    u.`id`,
                    u.`authlevel`,
                    u.`onlinetime`,
                    u.`ally_id`,
                    pr.`preference_vacation_mode`
                FROM `' . PLANETS . '` p
                INNER JOIN `' . USERS . '` u ON u.`id` = p.`planet_user_id`
                INNER JOIN `' . PREFERENCES . "` pr ON pr.`preference_user_id` = u.`id`
                WHERE p.`planet_galaxy` = '" . (int) $target_data['galaxy'] . "'
                    AND p.`planet_system` = '" . (int) $target_data['system'] . "'
                    AND p.`planet_planet` = '" . (int) $target_data['planet'] . "'
                    AND p.`planet_type` = '" . $targetType . "'"
            )
        );
        $target = $targetRow !== null ? (array) $targetRow : [];

        if ($target) {
            $this->_occupied_planet = true;

            // set target data
            $this->_target_data = $target;

            // validate owner
            if ($target['planet_user_id'] == $this->user['id']) {
                $this->_own_planet = true;
            }

            if ($target['planet_destroyed'] != 0) {
                Functions::redirect(self::REDIRECT_TARGET);
            }

            // set target owner
            $this->_fleet_data['fleet_target_owner'] = $target['planet_user_id'];
        }

        // set coords data
        $this->_fleet_data['fleet_start_galaxy'] = $this->planet['planet_galaxy'];
        $this->_fleet_data['fleet_start_system'] = $this->planet['planet_system'];
        $this->_fleet_data['fleet_start_planet'] = $this->planet['planet_planet'];
        $this->_fleet_data['fleet_start_type'] = $this->planet['planet_type'];
        $this->_fleet_data['fleet_end_galaxy'] = $target_data['galaxy'];
        $this->_fleet_data['fleet_end_system'] = $target_data['system'];
        $this->_fleet_data['fleet_end_planet'] = $target_data['planet'];
        $this->_fleet_data['fleet_end_type'] = $target_data['type'];
    }

    /**
     * Run multiple validations
     *
     * @return boolean
     */
    private function runValidations()
    {
        $validations = [
            'admin', 'ownVacations', 'targetVacations', 'acs', 'ships', 'mission', 'noobProtection', 'fleets', 'resources', 'time',
        ];

        foreach ($validations as $validation) {
            if (!$this->{'validate' . ucfirst($validation)}()) {
                return false;
            }
        }

        return true;
    }

    private function validateAdmin(): bool
    {
        // skip if it's our own planet or it's an empty planet
        if ($this->_own_planet or
            !$this->_occupied_planet) {
            return true;
        }

        if (app(SettingsService::class)->getInt('adm_attack') != 0 &&
            $this->_target_data['authlevel'] >= 1 &&
            $this->user['authlevel'] == 0) {
            $this->showMessage(
                __('game/fleet.fl_admins_cannot_be_attacked')
            );
        }

        return true;
    }

    private function validateOwnVacations(): bool
    {
        if ($this->userLibrary->isOnVacations($this->user)) {
            $this->showMessage(__('game/fleet.fl_vacation_mode_active'));
        }

        // set owner
        $this->_fleet_data['fleet_owner'] = $this->user['id'];

        return true;
    }

    private function validateTargetVacations(): bool
    {
        // skip if it's our own planet or it's an empty planet
        if ($this->_own_planet or
            !$this->_occupied_planet) {
            return true;
        }

        if (isset($this->_target_data) &&
            $this->userLibrary->isOnVacations($this->_target_data) &&
            $this->_clean_input_data['mission'] != Missions::RECYCLE) {
            $this->showMessage(__('game/fleet.fl_in_vacation_player'));
        }

        return true;
    }

    private function validateAcs(): bool
    {
        $target_data = $this->getTargetData();

        if ($target_data['group'] > 0 &&
            $this->_clean_input_data['mission'] == Missions::ACS) {
            $target_string = 'g' . (int) $target_data['galaxy'] .
            's' . (int) $target_data['system'] .
            'p' . (int) $target_data['planet'] .
            't' . (int) $target_data['type'];

            $acsCountRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT COUNT(`acs_id`) AS `acs_amount`
                    FROM `' . ACS . "`
                    WHERE `acs_id` = '" . (int) $target_data['group'] . "'"
                )
            );
            $acsCount = $acsCountRow !== null ? (int) $acsCountRow->acs_amount : 0;

            if ($target_data['acs_target'] == $target_string &&
                $acsCount > 0) {
                // set acs group
                $this->_fleet_data['fleet_group'] = $target_data['group'];

                return true;
            }

            return false;
        }

        return true;
    }

    private function validateShips(): bool
    {
        // post/session fleet
        $fleet = $this->getSessionShips();

        // planet ships
        $planetId = (int) $this->planet['planet_id'];
        $shipsRow = $planetId > 0 ? DB::selectOne(
            $this->prepareSql(
                'SELECT
                    s.`ship_small_cargo_ship`,
                    s.`ship_big_cargo_ship`,
                    s.`ship_light_fighter`,
                    s.`ship_heavy_fighter`,
                    s.`ship_cruiser`,
                    s.`ship_battleship`,
                    s.`ship_colony_ship`,
                    s.`ship_recycler`,
                    s.`ship_espionage_probe`,
                    s.`ship_bomber`,
                    s.`ship_solar_satellite`,
                    s.`ship_destroyer`,
                    s.`ship_deathstar`,
                    s.`ship_reaper`
                FROM `' . SHIPS . "` AS s
                WHERE s.`ship_planet_id` = '" . $planetId . "';"
            )
        ) : null;
        $planet_ships = $shipsRow !== null ? (array) $shipsRow : [];

        // objects
        $objects = $this->objects->getObjects();
        $price = $this->objects->getPrice();

        if ($fleet) {
            $total_ships = 0;

            foreach ($fleet as $ship_id => $amount) {
                if (!isset($planet_ships[$objects[$ship_id]]) or
                    ((int) $amount > $planet_ships[$objects[$ship_id]])) {
                    return false;
                }

                $total_ships += $amount;

                $this->_fleet_storage += $this->fleetsService->getMaxStorage(
                    $price[$ship_id]['capacity'],
                    $this->_research->getCurrentResearch()->getResearchHyperspaceTechnology()
                ) * $amount;
                $this->_fleet_ships[$objects[$ship_id]] = $amount;
            }

            $this->_fleet_data['fleet_amount'] = $total_ships;
            $this->_fleet_data['fleet_array'] = FleetsLib::setFleetShipsArray($fleet);

            return true;
        }

        return false;
    }

    private function validateMission(): bool
    {
        // post/session fleet
        $fleet = $this->getSessionShips();

        // clean data from post
        $data = $this->_clean_input_data;

        // target data
        $target = $this->_target_data;

        if (empty($data['mission'])) {
            Functions::redirect('game.php?page=fleet1');
        }

        if ($data['mission'] == Missions::ATTACK) {
            if ($this->_own_planet) {
                return false;
            }
        }

        if ($data['mission'] == Missions::SPY) {
            if (!isset($fleet[Ships::ship_espionage_probe])) {
                return false;
            }

            if ($this->_own_planet) {
                return false;
            }
        }

        if (
            $data['mission'] == Missions::DEPLOY &&
            !$this->_own_planet
        ) {
            $this->showMessage(
                $this->formatService->colorRed(__('game/fleet.fl_deploy_only_your_planets'))
            );
        }

        if ($data['mission'] == Missions::STAY) {
            $currentUserId = (int) $this->planet['planet_user_id'];
            $targetUserId = (int) $this->_target_data['planet_user_id'];
            $buddyRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT COUNT(*) AS buddies
                    FROM `' . BUDDY . "`
                    WHERE (
                        (
                            buddy_sender = '" . $currentUserId . "'
                            AND buddy_receiver = '" . $targetUserId . "'
                        )
                        OR (
                            buddy_sender = '" . $targetUserId . "'
                            AND buddy_receiver = '" . $currentUserId . "'
                        )
                    )
                    AND buddy_status = 1"
                )
            );
            $is_buddy = ($buddyRow !== null ? (int) $buddyRow->buddies : 0) >= 1;

            if ($this->_target_data['ally_id'] != $this->user['ally_id'] && !$is_buddy) {
                $this->showMessage(
                    $this->formatService->colorRed(__('game/fleet.fl_stay_not_on_enemy'))
                );
            }
        }

        if ($data['mission'] == Missions::COLONIZE) {
            if (!isset($fleet[Ships::ship_colony_ship])) {
                return false;
            }

            if ($this->_occupied_planet) {
                $this->showMessage(
                    $this->formatService->colorRed(__('game/fleet.fl_planet_populed'))
                );
            }
        }

        if ($data['mission'] == Missions::RECYCLE) {
            if ((count($target) <= 0) or
                ($target['planet_debris_metal'] == 0 &&
                    $target['planet_debris_crystal'] == 0 &&
                    time() > ($target['planet_invisible_start_time'] + DEBRIS_LIFE_TIME))) {
                return false;
            }
        }

        if ($data['mission'] == Missions::DESTROY) {
            if ($this->_own_planet or
                !$this->_occupied_planet or
                ($this->getTargetData()['type'] != PlanetTypes::MOON) or
                !isset($fleet[Ships::ship_deathstar])) {
                return false;
            }
        }

        if (
            $data['mission'] == Missions::EXPEDITION &&
            !$this->_occupied_planet
        ) {
            $expeditions = $this->_fleets->getExpeditionsCount();
            $max_expeditions = $this->fleetsService->getMaxExpeditions(
                $this->_research->getCurrentResearch()->getResearchAstrophysics()
            );

            if ($max_expeditions <= 0) {
                $this->showMessage(
                    $this->formatService->colorRed(__('game/fleet.fl_expedition_tech_required'))
                );
            }

            if ($max_expeditions <= $expeditions) {
                $this->showMessage(
                    $this->formatService->colorRed(__('game/fleet.fl_expedition_fleets_limit'))
                );
            }
        } else {
            if (
                $data['mission'] != Missions::COLONIZE &&
                !$this->_occupied_planet
            ) {
                return false;
            }
        }

        // add the fleet mission
        $this->_fleet_data['fleet_mission'] = $data['mission'];

        return true;
    }

    /**
     * Validate noob protection
     *
     * @return boolean
     */
    private function validateNoobProtection()
    {
        // skip if it's our own planet or it's an empty planet
        if (
            $this->_own_planet ||
            !$this->_occupied_planet
        ) {
            return true;
        }

        if (!$this->userLibrary->isInactive($this->_target_data)) {
            $noob = new NoobsProtectionLib();

            $points = $noob->returnPoints(
                $this->user['id'],
                $this->_target_data['id']
            );

            $user_points = $points['user_points'];
            $target_points = $points['target_points'];

            $disallow_weak = [
                Missions::ATTACK, Missions::ACS, Missions::SPY, Missions::DESTROY,
            ];

            $disallow_strong = [
                Missions::ATTACK, Missions::ACS, Missions::STAY, Missions::SPY, Missions::DESTROY,
            ];

            if ($noob->isWeak(intval($user_points), intval($target_points)) &&
                in_array($this->_clean_input_data['mission'], $disallow_weak)) {
                $this->showMessage(
                    $this->formatService->customColor(__('game/fleet.fl_week_player'), 'lime')
                );
            }

            if ($noob->isStrong(intval($user_points), intval($target_points)) &&
                in_array($this->_clean_input_data['mission'], $disallow_strong)) {
                $this->showMessage(
                    $this->formatService->colorRed(__('game/fleet.fl_strong_player'))
                );
            }
        }

        return true;
    }

    /**
     * Validate the amount of fleets
     *
     * @return boolean
     */
    private function validateFleets()
    {
        $fleets = $this->_fleets->getFleetsCount();

        $max_fleets = $this->fleetsService->getMaxFleets(
            $this->_research->getCurrentResearch()->getResearchComputerTechnology(),
            $this->officerService->isOfficerActive($this->_premium->getCurrentPremium()->getPremiumOfficierAdmiral(), time())
        );

        if ($max_fleets <= $fleets) {
            $this->showMessage(
                __('game/fleet.fl_no_slots')
            );
        }

        return true;
    }

    /**
     * Validate the resources
     *
     * @return boolean
     */
    private function validateResources()
    {
        $metal = $this->_clean_input_data['resource1'];
        $crystal = $this->_clean_input_data['resource2'];
        $deuterium = $this->_clean_input_data['resource3'];

        if ($metal + $crystal + $deuterium < 1 &&
            $this->_clean_input_data['mission'] == Missions::TRANSPORT) {
            $this->showMessage(
                $this->formatService->customColor(__('game/fleet.fl_empty_transport'), 'lime')
            );
        }

        $consumption = $this->getFleetData()['consumption'];
        $storage_needed = 0;

        $metal = max(0, $metal);
        $crystal = max(0, $crystal);
        $deuterium = max(0, $deuterium);

        if ($metal < 1) {
            $transport_metal = 0;
        } else {
            $transport_metal = $metal;
            $storage_needed += $transport_metal;
        }

        if ($crystal < 1) {
            $transport_crystal = 0;
        } else {
            $transport_crystal = $crystal;
            $storage_needed += $transport_crystal;
        }
        if ($deuterium < 1) {
            $transport_deuterium = 0;
        } else {
            $transport_deuterium = $deuterium;
            $storage_needed += $transport_deuterium;
        }

        $stock_metal = $this->planet['planet_metal'];
        $stock_crystal = $this->planet['planet_crystal'];
        $stock_deuterium = $this->planet['planet_deuterium'];
        $stock_deuterium -= $consumption;

        $stock_valid = false;

        if ($stock_metal >= $transport_metal) {
            if ($stock_crystal >= $transport_crystal) {
                if ($stock_deuterium >= $transport_deuterium) {
                    $stock_valid = true;
                }
            }
        }

        if (!$stock_valid) {
            $this->showMessage(
                $this->formatService->colorRed(__('game/fleet.fl_no_enought_deuterium') . $this->formatService->prettyNumber((int) $consumption))
            );
        }

        if ($storage_needed > $this->_fleet_storage) {
            $this->showMessage(
                $this->formatService->colorRed(__('game/fleet.fl_no_enought_cargo_capacity') . $this->formatService->prettyNumber($storage_needed - $this->_fleet_storage))
            );
        }

        // add resources to fleet
        $this->_fleet_data['fleet_resource_metal'] = $transport_metal;
        $this->_fleet_data['fleet_resource_crystal'] = $transport_crystal;
        $this->_fleet_data['fleet_resource_deuterium'] = $transport_deuterium;
        $this->_fleet_data['fleet_fuel'] = $consumption;

        return true;
    }

    /**
     * Validate fleet times
     *
     * @return boolean
     */
    private function validateTime()
    {
        $fleet_data = $this->getFleetData();

        $duration = floor($this->fleetsService->missionDuration(
            $fleet_data['speed'],
            $fleet_data['fleet_speed'],
            $fleet_data['distance'],
            Functions::fleetSpeedFactor()
        ));

        $base_time = time();
        $start_time = $duration + $base_time;
        $stay_duration = 0;
        $stay_time = 0;

        if ($this->_clean_input_data['mission'] == Missions::EXPEDITION) {
            $stay_duration = $this->_clean_input_data['expeditiontime'] * 3600;
            $stay_time = $start_time + $stay_duration;
        }

        if ($this->_clean_input_data['mission'] == Missions::STAY) {
            $stay_duration = $this->_clean_input_data['holdingtime'] * 3600;
            $stay_time = $start_time + $stay_duration;
        }

        $end_time = $stay_duration + (2 * $duration) + $base_time;

        if ($this->getTargetData()['group'] != 0) {
            $acsGroupId = (int) $this->getTargetData()['group'];
            $acsMaxRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT MAX(`fleet_start_time`) AS start_time
                    FROM `' . FLEETS . "`
                    WHERE `fleet_group` = '" . $acsGroupId . "';"
                )
            );
            $acs_start_time = $acsMaxRow !== null ? (string) $acsMaxRow->start_time : '';

            if ($acs_start_time >= $start_time) {
                $end_time += $acs_start_time - $start_time;
                $start_time = $acs_start_time;
            } else {
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . FLEETS . "` SET
                        `fleet_start_time` = '" . $start_time . "',
                        `fleet_end_time` = fleet_end_time + '" . ($start_time - $acs_start_time) . "'
                        WHERE `fleet_group` = '" . $acsGroupId . "';"
                    )
                );

                $end_time += $start_time - $acs_start_time;
            }
        }

        // add fleets times
        $this->_fleet_data['fleet_start_time'] = $start_time;
        $this->_fleet_data['fleet_end_time'] = $end_time;
        $this->_fleet_data['fleet_end_stay'] = $stay_time;

        return true;
    }

    private function getFleetData(): array
    {
        return session()->get('fleet_data');
    }

    private function getSessionShips(): array
    {
        return unserialize(base64_decode(str_rot13($this->getFleetData()['fleetarray'])));
    }

    private function getTargetData(): array
    {
        return session()->get('fleet_data')['target'];
    }

    private function showMessage($message): void
    {
        Functions::message(
            $message,
            self::REDIRECT_TARGET,
            3
        );
        exit();
    }

    /**
     * Send the fleet with the collected data
     *
     * @return void
     */
    private function sendFleet(): void
    {
        DB::transaction(function (): void {
            $sql = [];

            foreach ($this->_fleet_data as $field => $value) {
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

            foreach ($this->_fleet_ships as $field => $value) {
                $shipSql[] = '`' . $field . '` = `' . $field . "` - '" . $value . "'";
            }

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '` AS p
                    INNER JOIN `' . SHIPS . '` AS s ON s.`ship_planet_id` = p.`planet_id` SET
                    ' . join(', ', $shipSql) . ',
                    `planet_metal` = `planet_metal` - ' . $this->_fleet_data['fleet_resource_metal'] . ',
                    `planet_crystal` = `planet_crystal` - ' . $this->_fleet_data['fleet_resource_crystal'] . ',
                    `planet_deuterium` = `planet_deuterium` - ' . ($this->_fleet_data['fleet_resource_deuterium'] + $this->_fleet_data['fleet_fuel']) . '
                    WHERE `planet_id` = ' . $this->planet['planet_id'] . ';'
                )
            );
        });
    }
}
