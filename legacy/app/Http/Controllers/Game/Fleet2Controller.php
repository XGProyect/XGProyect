<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\Game\Formulas\FleetsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator as PlanetTypes;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Premium\Premium;
use Xgp\App\Libraries\Research\Researches;
use Xgp\App\Libraries\Users;
use Xgp\App\Libraries\Users\Shortcuts;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Fleet2Controller extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];
    private ?Researches $_research = null;
    private ?Premium $_premium = null;
    private array $_fleet_data = [
        'fleet_array' => [],
        'fleet_list' => '',
        'amount' => 0,
        'speed_all' => [],
    ];
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private OfficerService $officerService,
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

    private function setUpFleets(): void
    {
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
        /**
         * Parse the items
         */
        $page = [
            'fleet_block' => $this->buildFleetBlock(),
            'planet_types' => $this->buildPlanetTypesBlock(),
            'shortcuts' => $this->buildShortcutsBlock(),
            'colonies' => $this->buildColoniesBlock(),
            'acs' => $this->buildAcsBlock(),
        ];

        Template::legacyView(
            'fleet.fleet2_view',
            array_merge(
                $page,
                $this->setInputsData()
            )
        );
    }

    private function buildFleetBlock(): array
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
        $selected_fleet = filter_input_array(INPUT_POST);

        if ($ships != null) {
            foreach ($ships as $ship_name => $ship_amount) {
                if ($ship_amount != 0) {
                    $ship_id = array_search($ship_name, $objects);

                    if (!isset($selected_fleet['ship' . $ship_id]) or
                        $selected_fleet['ship' . $ship_id] == 0) {
                        continue;
                    }

                    $amount_to_set = intval($selected_fleet['ship' . $ship_id]);

                    if ($amount_to_set > $ship_amount) {
                        $amount_to_set = $ship_amount;
                    }

                    $this->_fleet_data['fleet_array'][$ship_id] = $amount_to_set;
                    $this->_fleet_data['fleet_list'] .= $ship_id . ',' . strval($amount_to_set) . ';';
                    $this->_fleet_data['amount'] += $amount_to_set;
                    $this->_fleet_data['speed_all'][$ship_id] = $this->fleetsService->getShipSpeed($ship_id, (int) $this->user['research_combustion_drive'], (int) $this->user['research_impulse_drive'], (int) $this->user['research_hyperspace_drive']);

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

    private function buildPlanetTypesBlock(): array
    {
        $planet_type = [
            'fl_planet' => PlanetTypes::PLANET,
            'fl_debris' => PlanetTypes::DEBRIS,
            'fl_moon' => PlanetTypes::MOON,
        ];

        $data = filter_input_array(INPUT_POST, [
            'planet_type' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 3],
            ],
        ]);

        $list_of_options = [];

        if ($data) {
            foreach ($planet_type as $label => $value) {
                $list_of_options[] = [
                    'value' => $value,
                    'selected' => ($value == $data['planet_type']) ? 'selected' : '',
                    'title' => __('game/fleet.' . $label),
                ];
            }
        }

        return $list_of_options;
    }

    private function buildShortcutsBlock(): string
    {
        if (!$this->officerService->isOfficerActive((int) $this->_premium->getCurrentPremium()->getPremiumOfficierCommander(), time())) {
            return '';
        }

        $shortcuts = new Shortcuts(
            $this->user['fleet_shortcuts']
        );

        $shortcuts_list = $shortcuts->getAllAsArray();

        if ($shortcuts_list) {
            $list_of_shortcuts = [];

            foreach ($shortcuts_list as $shortcut) {
                if ($shortcut != '') {
                    $description = $shortcut['name'] . ' ' . $this->formatService->prettyCoords(
                        (int)$shortcut['g'],
                        (int)$shortcut['s'],
                        (int)$shortcut['p']
                    ) . ' ' . __('game/global.planet_type_short')[$shortcut['pt']];

                    $list_of_shortcuts[] = [
                        'value' => $shortcut['g'] . ';' . $shortcut['s'] . ';' . $shortcut['p'] . ';' . $shortcut['pt'],
                        'selected' => '',
                        'title' => $description,
                    ];
                }
            }

            $shortcut_row = Template::render(
                'fleet/fleet2_shortcuts_row',
                [
                    'select' => 'shortcuts',
                    'options' => $list_of_shortcuts,
                ]
            );
        } else {
            $shortcut_row = Template::render(
                'fleet/fleet2_shortcuts_noshortcuts_row',
                ['shorcut_message' => __('game/fleet.fl_no_shortcuts')]
            );
        }

        return Template::render(
            'fleet.fleet2_shortcuts',
            [
                'shortcuts_rows' => $shortcut_row
            ]
        );
    }

    /**
     * Build the colony shortcuts block
     *
     * @return string
     */
    private function buildColoniesBlock()
    {
        $userId = (int) $this->user['id'];
        $planets = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        p.`planet_id`,
                        p.`planet_name`,
                        p.`planet_galaxy`,
                        p.`planet_system`,
                        p.`planet_planet`,
                        p.`planet_type`
                    FROM `' . PLANETS . "` AS p
                    WHERE p.`planet_user_id` = '" . $userId . "';"
                )
            )
        ) : [];
        $list_of_planets = [];

        if ($planets) {
            foreach ($planets as $planet) {
                $list_of_planets[] = [
                    'value' => $planet['planet_galaxy'] . ';' . $planet['planet_system'] . ';' . $planet['planet_planet'] . ';' . $planet['planet_type'],
                    'selected' => '',
                    'title' => $planet['planet_name'] . ' ' . $this->formatService->prettyCoords(
                        (int)$planet['planet_galaxy'],
                        (int)$planet['planet_system'],
                        (int)$planet['planet_planet']
                    ) . ($planet['planet_type'] == PlanetTypes::MOON ? ' (' . __('game/global.moon') . ')' : ''),
                ];
            }

            return Template::render(
                'fleet.fleet2_shortcuts_row',
                [
                    'select' => 'colonies',
                    'options' => $list_of_planets,
                ]
            );
        }

        return Template::render(
            'fleet.fleet2_shortcuts_noshortcuts_row',
            ['shorcut_message' => __('game/fleet.fl_no_colony')]
        );
    }

    /**
     * Build the acs shortcuts block
     *
     * @return string
     */
    private function buildAcsBlock()
    {
        $userId = (int) $this->user['id'];
        $current_acs = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT acs.*
                    FROM `' . ACS_MEMBERS . '` am
                    INNER JOIN `' . ACS . '` acs ON acs.`acs_id` = am.`acs_group_id`
                    INNER JOIN `' . FLEETS . "` f ON f.`fleet_group` = acs.`acs_id`
                    WHERE am.`acs_user_id` = '" . $userId . "';"
                )
            )
        );
        $acs_fleets = [];

        if ($current_acs) {
            foreach ($current_acs as $acs) {
                $acs_fleets[] = [
                    'galaxy' => $acs['acs_galaxy'],
                    'system' => $acs['acs_system'],
                    'planet' => $acs['acs_planet'],
                    'planet_type' => $acs['acs_planet_type'],
                    'id' => $acs['acs_id'],
                    'name' => $acs['acs_name'],
                ];
            }
        }

        return $acs_fleets;
    }

    /**
     * Set inputs data
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
            'planet_type' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 3],
            ],
            'target_mission' => FILTER_VALIDATE_INT,
        ]);

        if (is_null($data) or count($this->_fleet_data['speed_all']) <= 0) {
            Functions::redirect('game.php?page=fleet1');
        }

        // attach fleet data
        session([
            'fleet_data' => [
                'fleet_speed' => min($this->_fleet_data['speed_all']),
                'fleetarray' => str_rot13(base64_encode(serialize($this->_fleet_data['fleet_array']))),
            ]
        ]);

        return [
            'speedfactor' => Functions::fleetSpeedFactor(),
            'galaxy' => $this->planet['planet_galaxy'],
            'system' => $this->planet['planet_system'],
            'planet' => $this->planet['planet_planet'],
            'planet_type' => $this->planet['planet_type'],
            'galaxy_end' => $data['galaxy'] ?? $this->planet['planet_galaxy'],
            'system_end' => $data['system'] ?? $this->planet['planet_system'],
            'planet_end' => $data['planet'] ?? $this->planet['planet_planet'],
            'target_mission' => $data['target_mission'] ?? 0,
        ];
    }
}
