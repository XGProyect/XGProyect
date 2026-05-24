<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\Formulas\FleetsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\MissionsEnumerator as Missions;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator as PlanetTypes;
use Xgp\App\Core\Enumerators\ShipsEnumerator as Ships;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Research\Researches;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Fleet3Controller extends BaseController
{
    use PreparesLegacySql;

    public const REDIRECT_TARGET = 'game.php?page=fleet1';

    private array $user = [];
    private array $planet = [];
    private ?Researches $_research = null;
    private int $_current_mission = 0;
    private array $_allowed_missions = [];
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private FleetsService $fleetsService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Fleet));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->objects = new Objects();

        $this->setUpFleets();
        $this->buildPage();
    }

    /**
     * Creates a new ships object that will handle all the ships
     * creation methods and actions
     */
    private function setUpFleets(): void
    {
        $this->_research = new Researches(
            [$this->user],
            (int) $this->user['id']
        );
    }

    private function buildPage(): void
    {
        $inputsData = $this->setInputsData();

        Template::legacyView(
            'fleet/fleet3_view',
            array_merge(
                [
                    'fleet_block' => $this->buildFleetBlock(),
                    'title' => $this->buildTitleBlock(),
                    'mission_selector' => $this->buildMissionBlock(),
                    'stay_block' => $this->buildStayBlock(),
                ],
                $inputsData
            )
        );
    }

    /**
     * Build the fleet inputs block
     *
     * @return array
     */
    private function buildFleetBlock()
    {
        $objects = $this->objects->getObjects();
        $price = $this->objects->getPrice();

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
        $ships = $shipsRow !== null ? (array) $shipsRow : [];

        $list_of_ships = [];
        $selected_fleet = $this->getSessionShips();

        if ($ships != null) {
            foreach ($ships as $ship_name => $ship_amount) {
                if ($ship_amount != 0) {
                    $ship_id = array_search($ship_name, $objects);

                    if (
                        !isset($selected_fleet[$ship_id]) ||
                        $selected_fleet[$ship_id] == 0
                    ) {
                        continue;
                    }

                    $amount_to_set = $selected_fleet[$ship_id];

                    if ($amount_to_set > $ship_amount) {
                        $amount_to_set = $ship_amount;
                    }

                    $list_of_ships[] = [
                        'ship_id' => $ship_id,
                        'consumption' => $this->fleetsService->shipConsumption($ship_id, (int) $this->user['research_combustion_drive'], (int) $this->user['research_impulse_drive'], (int) $this->user['research_hyperspace_drive']),
                        'speed' => $this->fleetsService->getShipSpeed($ship_id, (int) $this->user['research_combustion_drive'], (int) $this->user['research_impulse_drive'], (int) $this->user['research_hyperspace_drive']),
                        'capacity' => $this->fleetsService->getMaxStorage(
                            $price[$ship_id]['capacity'],
                            $this->_research->getCurrentResearch()->getResearchHyperspaceTechnology()
                        ),
                        'ship' => $amount_to_set,
                    ];
                }
            }
        }

        return $list_of_ships;
    }

    private function buildTitleBlock(): string
    {
        return $this->formatService->prettyCoords(
            (int)$this->planet['planet_galaxy'],
            (int)$this->planet['planet_system'],
            (int)$this->planet['planet_planet']
        ) . ' - ' . __('game/global.planet_type')[$this->planet['planet_type']];
    }

    private function buildMissionBlock(): array
    {
        $missionsList = $this->getAllowedMissions();
        $missiongSelector = [];

        if (empty($this->_current_mission)) {
            $this->_current_mission = $missionsList[0];
        }

        if (count($missionsList)) {
            foreach ($missionsList as $mission) {
                $missiongSelector[] = [
                    'value' => $mission,
                    'mission' => __('game/missions.type_mission')[$mission],
                    'expedition_message' => $mission == Missions::EXPEDITION ? __('game/fleet.fl_expedition_alert_message') : '',
                    'id' => $mission == Missions::EXPEDITION ? ' ' : 'inpuT_' . $mission,
                    'checked' => $mission == $this->_current_mission ? ' checked="checked"' : '',
                ];
            }
        }

        return $missiongSelector;
    }

    private function buildStayBlock(): string
    {
        // by rule, expedition time is based on the astrophysics level, relation 1:1 level:hour
        $max_exp_time = $this->_research->getCurrentResearch()->getResearchAstrophysics();
        $hours = [0, 1, 2, 4, 8, 16, 32];
        $options = [];
        $stay_type = '';

        if (in_array(Missions::EXPEDITION, $this->_allowed_missions)) {
            $stay_type = 'expeditiontime';

            for ($i = 1; $i <= $max_exp_time; $i++) {
                $options[] = [
                    'value' => $i,
                    'selected' => $i == 1 ? ' selected' : '',
                ];
            }
        }

        if (in_array(Missions::STAY, $this->_allowed_missions)) {
            $stay_type = 'holdingtime';

            foreach ($hours as $hour) {
                $options[] = [
                    'value' => $hour,
                    'selected' => $hour == 1 ? ' selected' : '',
                ];
            }
        }

        if (count($options) > 0) {
            return Template::render(
                'fleet/fleet3_stay_row',
                [
                    'stay_type' => $stay_type,
                    'options' => $options,
                ]
            );
        }

        return '';
    }

    private function getAllowedMissions(): array
    {
        /**
         * rules
         */
        $ships_rules = [
            Ships::ship_small_cargo_ship => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_big_cargo_ship => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_light_fighter => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_heavy_fighter => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_cruiser => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_battleship => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_colony_ship => [
                Missions::DEPLOY, Missions::COLONIZE, Missions::EXPEDITION,
            ],
            Ships::ship_recycler => [
                Missions::DEPLOY, Missions::RECYCLE, Missions::EXPEDITION,
            ],
            Ships::ship_espionage_probe => [
                Missions::ATTACK, Missions::ACS, Missions::DEPLOY, Missions::STAY, Missions::SPY, Missions::EXPEDITION,
            ],
            Ships::ship_bomber => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_solar_satellite => [],
            Ships::ship_destroyer => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
            Ships::ship_deathstar => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::DESTROY, Missions::EXPEDITION,
            ],
            Ships::ship_reaper => [
                Missions::ATTACK, Missions::ACS, Missions::TRANSPORT, Missions::DEPLOY, Missions::STAY, Missions::EXPEDITION,
            ],
        ];

        $mission_rules = [
            PlanetTypes::PLANET => [
                'own' => [
                    Missions::TRANSPORT,
                    Missions::DEPLOY,
                ],
                'other' => [
                    Missions::ATTACK,
                    Missions::ACS,
                    Missions::TRANSPORT,
                    Missions::STAY,
                    Missions::SPY,
                    Missions::COLONIZE,
                ],
            ],
            PlanetTypes::DEBRIS => [
                'own' => [
                    Missions::RECYCLE,
                ],
                'other' => [
                    Missions::RECYCLE,
                ],
            ],
            PlanetTypes::MOON => [
                'own' => [
                    Missions::TRANSPORT,
                    Missions::DEPLOY,
                ],
                'other' => [
                    Missions::ATTACK,
                    Missions::ACS,
                    Missions::TRANSPORT,
                    Missions::STAY,
                    Missions::SPY,
                    Missions::DESTROY,
                ],
            ],
        ];

        /**
         * data
         */
        $ships = $this->getSessionShips();
        $acsId = (int) session('fleet_data')['target']['group'];
        $acsRow = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(`acs_id`) AS `acs_amount`
                FROM `' . ACS . "`
                WHERE `acs_id` = '" . $acsId . "'"
            )
        );
        $acs = $acsRow !== null ? (int) $acsRow->acs_amount : 0;

        $missions = [];
        $action_type = 'other';
        $ocuppied = false;

        $targetRow = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    p.`planet_user_id`,
                    u.`ally_id`
                FROM `' . PLANETS . '` AS p
                INNER JOIN `' . USERS . "` AS u
                    ON u.`id` = p.`planet_user_id`
                WHERE p.`planet_galaxy` = '" . (int) session('fleet_data')['target']['galaxy'] . "'
                    AND p.`planet_system` = '" . (int) session('fleet_data')['target']['system'] . "'
                    AND p.`planet_planet` = '" . (int) session('fleet_data')['target']['planet'] . "'
                    AND p.`planet_type` = '" . (int) session('fleet_data')['target']['type'] . "';"
            )
        );
        $selected_planet = $targetRow !== null ? (array) $targetRow : [];

        if ($selected_planet) {
            $ocuppied = true;

            if ($selected_planet['planet_user_id'] == $this->user['id']) {
                $action_type = 'own';
            }
        }

        if (session('fleet_data')['target']['planet'] == (MAX_PLANET_IN_SYSTEM + 1)) {
            $possible_missions = [Missions::EXPEDITION];
        } else {
            $possible_missions = $mission_rules[session('fleet_data')['target']['type']][$action_type];

            if (!$acs && in_array(Missions::ACS, $possible_missions)) {
                unset($possible_missions[array_search(Missions::ACS, $possible_missions)]);
            }

            if ($selected_planet && !$this->isFriendly($selected_planet) && in_array(Missions::STAY, $possible_missions)) {
                unset($possible_missions[array_search(Missions::STAY, $possible_missions)]);
            }

            if ($ocuppied && in_array(Missions::COLONIZE, $possible_missions)) {
                unset($possible_missions[array_search(Missions::COLONIZE, $possible_missions)]);
            }
        }

        if (count($ships) > 0) {
            foreach ($ships as $ship_id => $amount) {
                if ($amount > 0) {
                    $missions[] = array_intersect(
                        $ships_rules[$ship_id],
                        $possible_missions
                    );
                }
            }
        }

        // merge for each ship, but made them unique
        $missions_set = array_unique(array_merge(...$missions));

        // sort by value from lower to higher
        sort($missions_set);

        if (count($missions_set) <= 0) {
            Functions::redirect(self::REDIRECT_TARGET);
        }

        $this->_allowed_missions = $missions_set;

        return $missions_set;
    }

    /**
     * Set inputs data
     *
        * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
        *
     * @return array
     */
    private function setInputsData()
    {
        $data = filter_input_array(INPUT_POST, [
            'galaxy' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => MAX_GALAXY_IN_WORLD],
            ],
            'system' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => MAX_SYSTEM_IN_GALAXY],
            ],
            'planet' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => (MAX_PLANET_IN_SYSTEM + 1)],
            ],
            'planettype' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 3],
            ],
            'speed' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 10],
            ],
            'target_mission' => FILTER_VALIDATE_INT,
            'fleet_group' => FILTER_VALIDATE_INT,
            'acs_target' => FILTER_UNSAFE_RAW,
        ]);

        // remove values that din't pass the validation
        $data = array_diff($data, [null, false]);

        if (is_null($data) or count($data) != 8 or $this->isCurrentPlanet($data)) {
            Functions::redirect(self::REDIRECT_TARGET);
        }

        $this->_current_mission = $data['target_mission'];

        $distance = $this->fleetsService->targetDistance(
            (int) $this->planet['planet_galaxy'],
            (int) $data['galaxy'],
            (int) $this->planet['planet_system'],
            (int) $data['system'],
            (int) $this->planet['planet_planet'],
            (int) $data['planet']
        );

        $fleet = $this->getSessionShips();
        $Speed_factor = Functions::fleetSpeedFactor();
        $fleet_speed = $this->fleetsService->fleetMaxSpeed($fleet, (int) $this->user['research_combustion_drive'], (int) $this->user['research_impulse_drive'], (int) $this->user['research_hyperspace_drive']);

        $consumption = $this->fleetsService->fleetConsumption(
            $fleet,
            $Speed_factor,
            (int) $this->fleetsService->missionDuration(
                (int) $data['speed'],
                (int) min($fleet_speed),
                $distance,
                $Speed_factor
            ),
            $distance,
            (int) $this->user['research_combustion_drive'],
            (int) $this->user['research_impulse_drive'],
            (int) $this->user['research_hyperspace_drive']
        );

        // attach speed and target data
        session([
            'fleet_data' => array_merge(
                session('fleet_data'),
                [
                    'speed' => $data['speed'],
                    'target' => [
                        'galaxy' => $data['galaxy'],
                        'system' => $data['system'],
                        'planet' => $data['planet'],
                        'type' => $data['planettype'],
                        'group' => $data['fleet_group'],
                        'acs_target' => $data['acs_target'],
                    ],
                    'distance' => $distance,
                    'consumption' => $consumption,
                ]
            )
        ]);

        return [
            'this_metal' => floor($this->planet['planet_metal']),
            'this_crystal' => floor($this->planet['planet_crystal']),
            'this_deuterium' => floor($this->planet['planet_deuterium']),
            'this_galaxy' => $this->planet['planet_galaxy'],
            'this_system' => $this->planet['planet_system'],
            'this_planet' => $this->planet['planet_planet'],
            'this_planet_type' => $this->planet['planet_type'],
            'galaxy_end' => $data['galaxy'] ?? $this->planet['planet_galaxy'],
            'system_end' => $data['system'] ?? $this->planet['planet_system'],
            'planet_end' => $data['planet'] ?? $this->planet['planet_planet'],
            'planet_type_end' => $data['planettype'] ?? $this->planet['planet_type'],
            'speed' => $data['speed'] ?? 10,
            'speedfactor' => Functions::fleetSpeedFactor(),
        ];
    }

    private function getSessionShips()
    {
        if (isset(session('fleet_data')['fleetarray'])) {
            return unserialize(base64_decode(str_rot13(session('fleet_data')['fleetarray'])));
        }

        Functions::redirect(self::REDIRECT_TARGET);
    }

    private function isFriendly(array $target_planet): bool
    {
        $currentUserId = (int) $this->user['id'];
        $targetUserId = (int) $target_planet['planet_user_id'];
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

        if (
            !$is_buddy &&
            (
                ($target_planet['ally_id'] == 0 && $this->user['ally_id'] == 0) or
                ($target_planet['ally_id'] != $this->user['ally_id'])
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if it is the current planet
     *
     * @param array $target
     *
     * @return boolean
     */
    private function isCurrentPlanet(array $target): bool
    {
        return Functions::isCurrentPlanet(
            $this->planet,
            [
                'planet_galaxy' => $target['galaxy'],
                'planet_system' => $target['system'],
                'planet_planet' => $target['planet'],
                'planet_type' => $target['planettype'],
            ]
        );
    }
}
