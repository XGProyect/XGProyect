<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\ShipsEnumerator as Ships;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\Fleets;
use Xgp\App\Libraries\Premium\Premium;
use Xgp\App\Libraries\Research\Researches;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Fleet;

class Fleet1Controller extends BaseController
{
    public const MODULE_ID = 8;

    private array $user = [];
    private array $planet = [];
    private ?Fleets $_fleets = null;
    private ?Researches $_research = null;
    private ?Premium $_premium = null;
    private int $_ship_count = 0;
    private Fleet $fleetModel;
    private Objects $objects;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->fleetModel = new Fleet();
        $this->objects = new Objects();

        $this->setUpFleets();
        $this->buildPage();
    }

    private function setUpFleets(): void
    {
        $this->_fleets = new Fleets(
            $this->fleetModel->getAllFleetsByUserId($this->user['id']),
            $this->user['id']
        );

        $this->_research = new Researches(
            [$this->user],
            $this->user['id']
        );

        $this->_premium = new Premium(
            [$this->user],
            $this->user['id']
        );
    }

    private function buildPage(): void
    {
        $page = [
            'fleets' => $this->_fleets->getFleetsCount(),
            'max_fleets' => FleetsLib::getMaxFleets(
                $this->_research->getCurrentResearch()->getResearchComputerTechnology(),
                $this->_premium->getCurrentPremium()->getPremiumOfficierAdmiral()
            ),
            'expeditions' => $this->_fleets->getExpeditionsCount(),
            'max_expeditions' => FleetsLib::getMaxExpeditions(
                $this->_research->getCurrentResearch()->getResearchAstrophysics()
            ),
            'no_slot' => $this->buildNoSlotBlock(),
            'list_of_ships' => $this->buildListOfShips(),
            'none_max_selector' => $this->buildActionsBlock(),
            'no_ships' => $this->buildNoShipsBlock(),
            'continue_button' => $this->buildContinueBlock(),
        ];

        Template::legacyView(
            'fleet.fleet1_view',
            array_merge(
                $page,
                $this->setInputsData()
            )
        );
    }

    private function buildNoSlotBlock(): ?string
    {
        if (!$this->checkAvailableSlot()) {
            return Template::render('fleet.fleet1_noslots_row');
        }

        return null;
    }

    private function buildListOfShips(): array
    {
        $objects = $this->objects->getObjects();
        $price = $this->objects->getPrice();

        $ships = $this->fleetModel->getShipsByPlanetId($this->planet['planet_id']);

        $list_of_ships = [];

        if ($ships != null) {
            foreach ($ships as $ship_name => $ship_amount) {
                if ($ship_amount != 0) {
                    $this->_ship_count += $ship_amount;

                    $ship_id = array_search($ship_name, $objects);

                    $list_of_ships[] = [
                        'ship_name' => $this->buildShipName($ship_name, $ship_id),
                        'ship_amount' => $this->buildShipAmount($ship_amount),
                        'max_ships_link' => $this->buildMaxShipsLink($ship_id) ?? '-',
                        'ships_input' => $this->buildShipsInput($ship_id) ?? '-',
                        'ship_id' => $ship_id,
                        'max_ships' => $ship_amount,
                        'consumption' => FleetsLib::shipConsumption($ship_id, $this->user),
                        'speed' => FleetsLib::getShipSpeed($ship_id, $this->user),
                        'capacity' => FleetsLib::getMaxStorage(
                            $price[$ship_id]['capacity'],
                            $this->_research->getCurrentResearch()->getResearchHyperspaceTechnology()
                        ),
                    ];
                }
            }
        }

        return $list_of_ships;
    }

    private function buildShipName(string $ship_name, int $ship_id): string
    {
        $title = __('game/fleet.fl_speed_title') . FleetsLib::getShipSpeed($ship_id, $this->user);

        return UrlHelper::setUrl('', __('game/ships.' . $ship_name), $title);
    }

    /**
     * Build the ship amount block
     *
     * @param int $ship_amount Ship Amount
     *
     * @return string
     */
    private function buildShipAmount($ship_amount)
    {
        return FormatLib::prettyNumber($ship_amount);
    }

    /**
     * Build the ship max link
     *
     * @param int $ship_id Ship ID
     *
     * @return string
     */
    private function buildMaxShipsLink($ship_id)
    {
        if ($ship_id == Ships::ship_solar_satellite) {
            return null;
        }

        return UrlHelper::setUrl('#', __('game/fleet.fl_max'), '', 'onclick="javascript:maxShip(\'ship' . $ship_id . '\');"');
    }

    /**
     * Build the ship input field
     *
     * @param int $ship_id Ship ID
     *
     * @return string
     */
    private function buildShipsInput($ship_id)
    {
        if ($ship_id == Ships::ship_solar_satellite) {
            return null;
        }

        return '<input name="ship' . $ship_id . '" size="10" value="0" onfocus="javascript:if(this.value == \'0\') this.value=\'\';" onblur="javascript:if(this.value == \'0\') this.value=\'\';"/>';
    }

    /**
     * Build the actions block
     *
     * @return string
     */
    private function buildActionsBlock()
    {
        if ($this->_ship_count > 0 &&
            $this->checkAvailableSlot()) {
            return Template::render('fleet.fleet1_selector_row');
        }

        return '';
    }

    /**
     * Build the no ships block
     *
     * @return string
     */
    private function buildNoShipsBlock()
    {
        if ($this->_ship_count <= 0) {
            return Template::render('fleet.fleet1_noships_row');
        }

        return '';
    }

    /**
     * Build the continue button block
     *
     * @return string
     */
    private function buildContinueBlock()
    {
        if ($this->_ship_count > 0 &&
            $this->checkAvailableSlot()) {
            return Template::render('fleet.fleet1_button');
        }

        return '';
    }

    /**
     * Check if we can send the fleet
     *
     * @return boolean
     */
    private function checkAvailableSlot()
    {
        return (FleetsLib::getMaxFleets(
            $this->_research->getCurrentResearch()->getResearchComputerTechnology(),
            $this->_premium->getCurrentPremium()->getPremiumOfficierAdmiral()
        ) > $this->_fleets->getFleetsCount());
    }

    /**
     * Set inputs data
     *
     * @return array
     */
    private function setInputsData()
    {
        $data = filter_input_array(INPUT_GET, [
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
            'target_mission' => FILTER_VALIDATE_INT,
        ]);

        // always reset, and define as array
        session('fleet_data', []);

        return [
            'galaxy' => $data['galaxy'] ?? $this->planet['planet_galaxy'],
            'system' => $data['system'] ?? $this->planet['planet_system'],
            'planet' => $data['planet'] ?? $this->planet['planet_planet'],
            'planettype' => $data['planettype'] ?? $this->planet['planet_type'],
            'target_mission' => $data['target_mission'] ?? 0,
        ];
    }
}
