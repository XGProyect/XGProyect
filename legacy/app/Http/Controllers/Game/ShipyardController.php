<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\DefensesEnumerator as Defenses;
use Xgp\App\Core\Enumerators\ShipsEnumerator as Ships;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Shipyard;

class ShipyardController extends BaseController
{
    public const MODULE_ID = 7;

    private array $user = [];
    private array $planet = [];
    protected string $page = 'shipyard';
    protected string $langFile = 'ships';
    protected array $allowedStructures = [
        Ships::ship_small_cargo_ship,
        Ships::ship_big_cargo_ship,
        Ships::ship_light_fighter,
        Ships::ship_heavy_fighter,
        Ships::ship_cruiser,
        Ships::ship_battleship,
        Ships::ship_colony_ship,
        Ships::ship_recycler,
        Ships::ship_espionage_probe,
        Ships::ship_bomber,
        Ships::ship_solar_satellite,
        Ships::ship_destroyer,
        Ships::ship_deathstar,
        Ships::ship_battlecruiser,
    ];
    protected array $missiles = [];

    private array $resources_consumed = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0,
    ];
    private bool $building_in_progress = false;
    private Shipyard $shipyardModel;
    private Objects $objects;
    private Users $userLibrary;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->shipyardModel = new Shipyard();
        $this->objects = new Objects();
        $this->userLibrary = new Users();

        $this->setUpShipyard();
        $this->runAction();

        Template::legacyView(
            'shipyard.view',
            [
                'message' => $this->showShipyardUpgradeMessage(),
                'list_of_items' => $this->buildListOfItems(),
                'build_button' => $this->getBuildItemsButton(),
                'building_list' => $this->buildItemsQueue(),
            ]
        );
    }

    private function setUpShipyard(): void
    {
        $this->showShipyardRequiredMessage();
        $this->setAllowedStructures();
        $this->isAnyFacilityWorking();
    }

    private function runAction(): void
    {
        $items = filter_input(INPUT_POST, 'fmenge', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (!is_null($items) && $items !== false) {
            $total_items_to_build = 0;
            $shipyard_queue = '';

            // set resources before build
            $this->resources_consumed['metal'] = $this->planet['planet_metal'];
            $this->resources_consumed['crystal'] = $this->planet['planet_crystal'];
            $this->resources_consumed['deuterium'] = $this->planet['planet_deuterium'];

            foreach ($items as $item => $amount) {
                // avoid elements that not match the criteria
                if (!in_array($item, $this->allowedStructures)
                    or ($amount <= 0)
                    or $this->isShieldDomeAvailable($item)) {
                    continue;
                }

                $item = (int) $item;
                $amount = (int) $amount;

                // calculate the max amount of elements that can be build
                $amount = $this->getMaxBuildableItems($item, $amount);

                // If after every validation, the amount of items to build, is more than 0
                if ($amount > 0) {
                    $resources_needed = $this->getItemNeededResourcesByAmount($item, $amount);
                    $this->resources_consumed['metal'] -= $resources_needed['metal'];
                    $this->resources_consumed['crystal'] -= $resources_needed['crystal'];
                    $this->resources_consumed['deuterium'] -= $resources_needed['deuterium'];
                    $shipyard_queue .= $item . ',' . $amount . ';';
                    $total_items_to_build += $amount;
                }
            }

            if ($total_items_to_build > 0) {
                $this->shipyardModel->insertItemsToBuild(
                    $this->resources_consumed,
                    $shipyard_queue,
                    $this->planet['planet_id']
                );
            }

            Functions::redirect('game.php?page=' . $this->page);
        }
    }

    private function showShipyardUpgradeMessage(): string
    {
        if ($this->building_in_progress) {
            return FormatLib::colorRed(__('game/shipyard.sy_building_shipyard'));
        }

        return '';
    }

    private function buildListOfItems(): array
    {
        $shipyardList = [];

        if (!is_null($this->allowedStructures)) {
            foreach ($this->allowedStructures as $itemId) {
                $shipyardList[] = $this->setListOfShipyardItem($itemId);
            }
        }

        return $shipyardList;
    }

    private function setListOfShipyardItem(int $itemId): array
    {
        $item_to_parse = [];

        $item_to_parse['element'] = $itemId;
        $item_to_parse['element_name'] = __('game/' . $this->langFile . '.' . $this->objects->getObjects($itemId));
        $item_to_parse['element_description'] = $this->getItemDescription($itemId);
        $item_to_parse['element_price'] = $this->getItemPriceWithFormat($itemId);
        $item_to_parse['building_time'] = $this->getItemTimeWithFormat($itemId);
        $item_to_parse['element_nbre'] = $this->getItemAmountWithFormat($itemId);
        $item_to_parse['add_element'] = $this->getItemInsertBlock($itemId);

        return $item_to_parse;
    }

    private function getItemDescription(int $itemId): string
    {
        if ($itemId == Defenses::defense_interplanetary_missile) {
            return strtr(
                __('game/shipyard.descriptions')[$this->objects->getObjects($itemId)],
                ['%s' => Formulas::missileRange($this->user['research_impulse_drive'])]
            );
        }

        return __('game/shipyard.descriptions')[$this->objects->getObjects($itemId)];
    }

    private function getItemPriceWithFormat(int $itemId): string
    {
        return DevelopmentsLib::formatedDevelopmentPrice(
            $this->user,
            $this->planet,
            $itemId,
            false
        );
    }

    private function getItemTimeWithFormat(int $itemId): string
    {
        return DevelopmentsLib::formatedDevelopmentTime(
            $this->getItemTime($itemId),
            __('game/shipyard.sy_time')
        );
    }

    private function getItemTime(int $itemId): int
    {
        return DevelopmentsLib::developmentTime(
            $this->user,
            $this->planet,
            $itemId
        );
    }

    private function getItemAmountWithFormat(int $itemId): string
    {
        $amount = $this->getItemAmount($itemId);

        if ($amount == 0) {
            return '';
        }

        return ' (' . __('game/shipyard.sy_available') . FormatLib::prettyNumber($amount) . ')';
    }

    private function getItemInsertBlock(int $itemId): string
    {
        if (!$this->building_in_progress && !$this->userLibrary->isOnVacations($this->user)
        ) {
            if ($this->isShieldDomeAvailable($itemId)) {
                return FormatLib::colorRed(__('game/shipyard.sy_protection_shield_only_one'));
            } else {
                $box_data = [];
                $box_data['item_id'] = $itemId;
                $box_data['tab_index'] = $itemId;

                return Template::render(
                    'shipyard/shipyard_build_box',
                    $box_data
                );
            }
        }

        return '';
    }

    private function getItemAmount(int $itemId): int
    {
        return $this->planet[$this->objects->getObjects()[$itemId]];
    }

    private function getBuildItemsButton(): string
    {
        if (!$this->building_in_progress && !$this->userLibrary->isOnVacations($this->user)) {
            return Template::render(
                'shipyard/shipyard_build_button'
            );
        }

        return '';
    }

    private function buildItemsQueue(): string
    {
        $queue = explode(';', $this->planet['planet_b_hangar_id']);
        $queue_time = 0;
        $item_time_per_type = '';
        $item_name_per_type = '';
        $item_amount_per_type = '';

        if (!is_null($queue[0]) && !empty($queue[0])) {
            foreach ($queue as $item_data) {
                if (!empty($item_data)) {
                    $item_values = explode(',', $item_data);

                    // $item_values[0] = item ID
                    $item_time = $this->getItemTime($item_values[0]);

                    $type = strpos($this->objects->getObjects($item_values[0]), 'ship') !== false ? 'ships' : 'defenses';

                    $item_time_per_type .= $item_time . ',';
                    $item_name_per_type .= '\'' . html_entity_decode(
                        __('game/' . $type . '.' . $this->objects->getObjects($item_values[0])),
                        ENT_COMPAT,
                        'utf-8'
                    ) . '\',';
                    $item_amount_per_type .= $item_values[1] . ',';

                    // $item_values[1] = amount
                    $queue_time += $item_time * $item_values[1];
                }
            }

            $block['a'] = $item_amount_per_type;
            $block['b'] = $item_name_per_type;
            $block['c'] = $item_time_per_type;
            $block['b_hangar_id_plus'] = $this->planet['planet_b_hangar'];
            $block['current_page'] = $this->page;
            $block['pretty_time_b_hangar'] = FormatLib::prettyTime($queue_time - $this->planet['planet_b_hangar']);

            return Template::render('shipyard/shipyard_script', $block);
        }

        return '';
    }

    private function setAllowedStructures(): void
    {
        $this->allowedStructures = array_filter(
            $this->allowedStructures,
            function ($value) {
                return DevelopmentsLib::isDevelopmentAllowed(
                    $this->user,
                    $this->planet,
                    $value
                );
            }
        );
    }

    private function showShipyardRequiredMessage(): void
    {
        if ($this->planet[$this->objects->getObjects(21)] == 0) {
            Functions::message(__('game/shipyard.sy_shipyard_required'));
        }
    }

    private function isAnyFacilityWorking(): void
    {
        // by default is false ...
        $this->building_in_progress = false;

        // unless ...
        if ($this->planet['planet_b_building_id'] != 0) {
            $queue = explode(';', $this->planet['planet_b_building_id']);
            $not_allowed = [14, 15, 21];

            foreach ($queue as $building_data) {
                $building = explode(',', $building_data);

                // $building[0] = Building ID
                if (in_array($building[0], $not_allowed)) {
                    $this->building_in_progress = true;
                    break; // any of the "banned" buildings is being built
                }
            }
        }
    }

    private function getMaxBuildableItems(int $itemId, int $amount_requested): int
    {
        // set construction limit based on resources
        $max_by_resource = $this->getMaxBuildableItemsByResource($itemId);

        // set construction limit based system config
        $max_by_system = $this->getMaxBuildableItemsBySystemLimit();

        // set the construction limit for shields
        if (in_array($itemId, [Defenses::defense_small_shield_dome, Defenses::defense_large_shield_dome])) {
            $max_shields = $this->getShieldDomeItemLimit($itemId);

            if ($amount_requested > $max_shields) {
                $amount_requested = $max_shields;
            }
        }

        // set the construction limit for missiles
        if (in_array($itemId, [Defenses::defense_anti_ballistic_missile, Defenses::defense_interplanetary_missile])) {
            $max_missiles = $this->getMissilesItemLimit($itemId, $amount_requested);

            if ($amount_requested > $max_missiles) {
                $amount_requested = $max_missiles;
            }
        }

        //validations
        if ($amount_requested > $max_by_resource) {
            $amount_requested = $max_by_resource;
        }

        if ($amount_requested > $max_by_system) {
            $amount_requested = $max_by_system;
        }

        // last verification for missiles,
        // I'm sure I can do all this process better
        if (in_array($itemId, [Defenses::defense_anti_ballistic_missile, Defenses::defense_interplanetary_missile])) {
            // keep track of the amount of missiles
            $this->missiles[$itemId] += $amount_requested;
        }

        return $amount_requested;
    }

    private function getMaxBuildableItemsByResource(int $itemId): int
    {
        $buildable = [];
        $price_metal = $this->objects->getPrice($itemId, 'metal');
        $price_crystal = $this->objects->getPrice($itemId, 'crystal');
        $price_deuterium = $this->objects->getPrice($itemId, 'deuterium');

        if ($price_metal != 0) {
            $buildable['metal'] = floor($this->resources_consumed['metal'] / $price_metal);
        }

        if ($price_crystal != 0) {
            $buildable['crystal'] = floor($this->resources_consumed['crystal'] / $price_crystal);
        }

        if ($price_deuterium != 0) {
            $buildable['deuterium'] = floor($this->resources_consumed['deuterium'] / $price_deuterium);
        }

        return max(min($buildable), 0);
    }

    private function getMaxBuildableItemsBySystemLimit(): int
    {
        return MAX_FLEET_OR_DEFS_PER_ROW;
    }

    private function getShieldDomeItemLimit(int $itemId): int
    {
        // set construction limit for shield dome
        $shields_ids = [Defenses::defense_small_shield_dome, Defenses::defense_large_shield_dome];

        if (in_array($itemId, $shields_ids)) {
            if (!$this->isShieldDomeAvailable($itemId)) {
                return 1;
            }
        }

        return 0;
    }

    private function getMissilesItemLimit(int $itemId, int $amount_requested): int
    {
        // calculate missile amount
        $this->calculateMissilesAmount();

        // start applying formulas
        $silo_size = $this->planet[$this->objects->getObjects(44)] * 10;
        $taken_space = $this->missiles[Defenses::defense_anti_ballistic_missile] + ($this->missiles[Defenses::defense_interplanetary_missile] * 2);
        $max_amount = $silo_size - $taken_space;
        $amount = 0;

        if ($itemId == Defenses::defense_anti_ballistic_missile) {
            $amount = $max_amount;
        }

        if ($itemId == Defenses::defense_interplanetary_missile) {
            $amount = floor($max_amount / 2);
        }

        if ($amount_requested > $amount) {
            $amount_requested = $amount;
        }

        return $amount_requested;
    }

    private function getItemNeededResourcesByAmount(int $itemId, int $amount): array
    {
        return [
            'metal' => ($this->objects->getPrice($itemId, 'metal') * $amount),
            'crystal' => ($this->objects->getPrice($itemId, 'crystal') * $amount),
            'deuterium' => ($this->objects->getPrice($itemId, 'deuterium') * $amount),
        ];
    }

    private function isShieldDomeAvailable(int $itemId): bool
    {
        if (in_array($itemId, [Defenses::defense_small_shield_dome, Defenses::defense_large_shield_dome])) {
            // check if something is already built
            if ($this->planet[$this->objects->getObjects($itemId)] >= 1) {
                return true;
            }

            // check if something is being built
            $in_queue = strpos($this->planet['planet_b_hangar_id'], $itemId . ',');

            if ($in_queue !== false) {
                return true;
            }
        }

        return false;
    }

    private function calculateMissilesAmount(): void
    {
        // get the amount of missiles stored in the planet
        $planet_missiles = [
            Defenses::defense_anti_ballistic_missile => $this->planet[$this->objects->getObjects(Defenses::defense_anti_ballistic_missile)],
            Defenses::defense_interplanetary_missile => $this->planet[$this->objects->getObjects(Defenses::defense_interplanetary_missile)],
        ];

        // get the amount of missiles in the current queue
        $current_queue = $this->processQueueToArray();
        $queue_missiles = [
            Defenses::defense_anti_ballistic_missile => 0,
            Defenses::defense_interplanetary_missile => 0,
        ];

        foreach ($current_queue as $item => $amount) {
            if ($item == Defenses::defense_anti_ballistic_missile
                or $item == Defenses::defense_interplanetary_missile) {
                $queue_missiles[$item] += $amount;
            }
        }

        // add the amount of missiles stored in the planet, and the amount of
        // missiles in the current queue, and finally the amount of missiles in
        //  the queue that's being developed.
        $this->missiles[Defenses::defense_anti_ballistic_missile] += $planet_missiles[Defenses::defense_anti_ballistic_missile] + $queue_missiles[Defenses::defense_anti_ballistic_missile];
        $this->missiles[Defenses::defense_interplanetary_missile] += $planet_missiles[Defenses::defense_interplanetary_missile] + $queue_missiles[Defenses::defense_interplanetary_missile];
    }

    private function processQueueToArray(): array
    {
        $queue = explode(';', $this->planet['planet_b_hangar_id']);
        $array_queue = [];

        if (!empty($queue[0])) {
            foreach ($queue as $item_data) {
                if (!empty($item_data[0])) {
                    $item = explode(',', $item_data);

                    if (!isset($array_queue[$item[0]])) {
                        $array_queue[$item[0]] = 0;
                    }

                    $array_queue[$item[0]] += $item[1];
                }
            }
        }

        return $array_queue;
    }
}
