<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Missions;

use App\Models\UsersStatistics;
use App\Services\FormatService;
use App\Services\Game\Formulas\ExpeditionService;
use App\Services\Game\Formulas\FleetsService;
use Xgp\App\Core\Objects;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Expedition extends Missions
{
    private int $resourceExpeditionPoints = 0;
    private int $shipExpeditionPoints = 0;
    private int $fleetCapacity = 0;

    public function __construct(
        private ExpeditionService $expeditionService,
        private FormatService $formatService
    ) {
        parent::__construct();
    }

    public function expeditionMission(array $fleet): void
    {
        // do mission
        if (parent::canStartMission($fleet)) {
            $this->setExpeditionPoints($fleet);

            switch ($this->expeditionService->getExpeditionResult()) {
                case 'darkMatter':
                    $this->resultDarkMatter($fleet);
                    break;
                case 'ships':
                    $this->resultShips($fleet);
                    break;
                case 'resources':
                    $this->resultResources($fleet);
                    break;
                case 'pirates':
                    //$this->resultPirates($fleet);
                    $this->resultNothing($fleet);
                    break;
                case 'aliens':
                    //$this->resultAliens($fleet);
                    $this->resultNothing($fleet);
                    break;
                case 'delay':
                    $this->resultDelay($fleet);
                    break;
                case 'early':
                    $this->resultEarly($fleet);
                    break;
                case 'merchant':
                    //$this->resultMerchant($fleet);
                    $this->resultNothing($fleet);
                    break;
                case 'blackHole':
                    $this->resultBlackHole($fleet);
                    break;
                case 'nothing':
                default:
                    $this->resultNothing($fleet);
                    break;
            }
        } elseif (parent::canCompleteMission($fleet)) {
            $fleetUsedStorage = $fleet['fleet_resource_metal'] + $fleet['fleet_resource_crystal'] + $fleet['fleet_resource_deuterium'];

            if ($fleetUsedStorage === 0) {
                $message = sprintf(
                    __('game/missions.mi_fleet_back_without_resources'),
                    $fleet['planet_end_name'],
                    $this->formatService->prettyCoords((int)$fleet['fleet_end_galaxy'], (int)$fleet['fleet_end_system'], (int)$fleet['fleet_end_planet']),
                    $fleet['planet_start_name'],
                    $this->formatService->prettyCoords((int)$fleet['fleet_start_galaxy'], (int)$fleet['fleet_start_system'], (int)$fleet['fleet_start_planet']),
                );

                $this->expeditionMessage(
                    (int) $fleet['fleet_owner'],
                    $message,
                    (int) $fleet['fleet_end_stay'],
                    [
                        'galaxy' => $fleet['fleet_end_galaxy'],
                        'system' => $fleet['fleet_end_system'],
                        'planet' => $fleet['fleet_end_planet'],
                    ]
                );
            } else {
                $message = sprintf(
                    __('game/missions.mi_fleet_back_with_resources'),
                    $fleet['planet_end_name'],
                    $this->formatService->prettyCoords((int) $fleet['fleet_end_galaxy'], (int) $fleet['fleet_end_system'], (int) $fleet['fleet_end_planet']),
                    $fleet['planet_start_name'],
                    $this->formatService->prettyCoords((int) $fleet['fleet_start_galaxy'], (int) $fleet['fleet_start_system'], (int) $fleet['fleet_start_planet']),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_metal']),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_crystal']),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_deuterium'])
                );

                $this->expeditionMessage(
                    (int) $fleet['fleet_owner'],
                    $message,
                    (int) $fleet['fleet_end_stay'],
                    [
                        'galaxy' => $fleet['fleet_end_galaxy'],
                        'system' => $fleet['fleet_end_system'],
                        'planet' => $fleet['fleet_end_planet'],
                    ]
                );
            }

            parent::restoreFleet($fleet, true);
            parent::removeFleet($fleet['fleet_id']);
        }
    }

    private function setExpeditionPoints(array $fleet): void
    {
        $priceList = Objects::getInstance()->getPrice();
        $expeditionPoints = 0;

        foreach (FleetsLib::getFleetShipsArray($fleet['fleet_array']) as $id => $count) {
            if (in_array($id, $this->expeditionService->getPossibleShips())) {
                $expeditionPoints += $this->expeditionService->calculateExpeditionPoints(
                    ($priceList[$id]['metal'] + $priceList[$id]['crystal'])
                ) * $count;
            }

            $this->fleetCapacity += app(FleetsService::class)->getMaxStorage(
                (int) $priceList[$id]['capacity'],
                (int) $fleet['research_hyperspace_technology']
            ) * $count;
        }

        $topPlayerPoints = UsersStatistics::max('user_statistic_total_points');

        $maxResourceFindExpeditionPoints = $this->expeditionService->getMaxExpeditionPoints(
            $topPlayerPoints
        );
        $maxShipsFindExpeditionPoints = $this->expeditionService->getMaxShipsExpeditionPoints(
            $topPlayerPoints
        );

        $this->resourceExpeditionPoints = $expeditionPoints;
        $this->shipExpeditionPoints = $expeditionPoints;

        // limit the amount of resources that can be found
        if ($expeditionPoints > $maxResourceFindExpeditionPoints) {
            $this->resourceExpeditionPoints = $maxResourceFindExpeditionPoints;
        }

        // limit the amount of ships that can be found
        if ($expeditionPoints > $maxShipsFindExpeditionPoints) {
            $this->shipExpeditionPoints = $maxShipsFindExpeditionPoints;
        }
    }

    /**
     * @todo needs polishing, there are 3 types of packages
     * small package: 300-400 DM
     * medium package: 500-700 DM
     * large package: 1.000-1.800 DM
     *
     * needs review because I replicated previous used logic for resources
     * I couldn't find any rule behind this...
     */
    private function resultDarkMatter(array $fleet): void
    {
        $darkMatterFound = $this->expeditionService->getDarkMatterSourceSize(
            $this->expeditionService->calculateDarkMatterSourceSize()
        );

        $this->expeditionMessage(
            (int) $fleet['fleet_owner'],
            __('game/expedition.exp_dm_' . mt_rand(1, 5)),
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );

        $this->updateDarkMatter((int) $fleet['fleet_owner'], $darkMatterFound);

        parent::returnFleet($fleet['fleet_id']);
    }

    /**
     * @todo probably not 100% like the original game
     */
    private function resultShips(array $fleet): void
    {
        $shipsRatio = $this->expeditionService->getShipsObtainableChances();
        $foundChance = $this->shipExpeditionPoints / $fleet['fleet_amount'];
        $currentFleet = FleetsLib::getFleetShipsArray($fleet['fleet_array']);
        $foundShip = [];

        for ($ship = 202; $ship <= 215; $ship++) {
            if (isset($currentFleet[$ship]) && $currentFleet[$ship] != 0) {
                $foundShip[$ship] = round($currentFleet[$ship] * $shipsRatio[$ship] * $foundChance) + 1;

                if ($foundShip[$ship] > 0) {
                    $currentFleet[$ship] += $foundShip[$ship];
                }
            }
        }

        $newShips = [];
        $found_ship_message = '';

        foreach ($currentFleet as $ship => $count) {
            if ($count > 0) {
                $newShips[$ship] = $count;
            }
        }

        if ($foundShip != null) {
            foreach ($foundShip as $ship => $count) {
                if ($count != 0) {
                    $found_ship_message .= __('game/ships.' . $this->resource[$ship]) . ': ' . $count . '<br>';
                }
            }
        }

        $this->updateFleetArrayById([
            'ships' => FleetsLib::setFleetShipsArray($newShips),
            'fleet_id' => $fleet['fleet_id'],
        ]);

        $message = sprintf(
            __('game/expedition.exp_new_ships_' . mt_rand(1, 5)),
            $found_ship_message
        );

        $this->expeditionMessage(
            $fleet['fleet_owner'],
            $message,
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );
    }

    private function resultResources(array $fleet): void
    {
        // fleet capacity
        $fleetUsedStorage = $fleet['fleet_resource_metal'] + $fleet['fleet_resource_crystal'] + $fleet['fleet_resource_deuterium'];
        $fleetMaxCapacity = $this->fleetCapacity - $fleetUsedStorage;

        // expedition resources obtained calculations
        $typeObtained = $this->expeditionService->calculateResourceTypeObtained();
        $foundAmount = $this->expeditionService->getResourceFoundAmount(
            $this->expeditionService->getResourceSourceSizeMultChances(
                $typeObtained
            ),
            $this->resourceExpeditionPoints,
            $typeObtained
        );

        if ($foundAmount > $fleetMaxCapacity) {
            $fillFleetStorage = $fleetMaxCapacity;
        } else {
            $fillFleetStorage = $foundAmount;
        }

        $this->updateFleetResourcesById(
            (int) $fleet['fleet_id'],
            $typeObtained,
            $fillFleetStorage
        );

        $this->expeditionMessage(
            (int) $fleet['fleet_owner'],
            __('game/expedition.exp_new_resources_' . mt_rand(1, 4)),
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );

        parent::returnFleet($fleet['fleet_id']);
    }

    /**
     * @todo implement
     */
    private function resultPirates(array $fleet): void
    {
    }

    /**
     * @todo implement
     */
    private function resultAliens(array $fleet): void
    {
    }

    /**
     * @todo probably not 100% like the original game
     */
    private function resultDelay(array $fleet): void
    {
        $fleetDelayMultiplier = $this->expeditionService->getFleetDeplay();
        $returnTime = (int) $fleet['fleet_end_time'] - (int) $fleet['fleet_end_stay'];

        $this->updateFleetEndTime(
            (int) $fleet['fleet_id'],
            ($fleet['fleet_end_time'] + ($returnTime * $fleetDelayMultiplier))
        );

        $this->expeditionMessage(
            (int) $fleet['fleet_owner'],
            __('game/expedition.exp_delay_' . mt_rand(1, 5)),
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );

        parent::returnFleet($fleet['fleet_id']);
    }

    /**
     * @todo probably not 100% like the original game
     */
    private function resultEarly(array $fleet): void
    {
        $returnTime = (int) $fleet['fleet_end_time'] - (int) $fleet['fleet_end_stay'];

        $this->updateFleetEndTime(
            (int) $fleet['fleet_id'],
            ($fleet['fleet_end_time'] - ($returnTime / 2))
        );

        $this->expeditionMessage(
            (int) $fleet['fleet_owner'],
            __('game/expedition.exp_early_' . mt_rand(1, 5)),
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );

        parent::returnFleet($fleet['fleet_id']);
    }

    /**
     * @todo implement
     */
    private function resultMerchant(array $fleet): void
    {
    }

    /**
     * @todo probably not 100% like the original game
     */
    private function resultBlackHole(array $fleet): void
    {
        $lostChances = (mt_rand(0, 3) * 33 + 1) / 100;

        if ($lostChances == 1) {
            $this->expeditionMessage(
                $fleet['fleet_owner'],
                __('game/expedition.exp_lost_1'),
                (int) $fleet['fleet_end_stay'],
                [
                    'galaxy' => $fleet['fleet_end_galaxy'],
                    'system' => $fleet['fleet_end_system'],
                    'planet' => $fleet['fleet_end_planet'],
                ]
            );

            $this->updateLostShipsAndDefensePoints(
                $fleet['fleet_owner'],
                FleetsLib::getFleetShipsArray($fleet['fleet_array'])
            );
            parent::removeFleet($fleet['fleet_id']);
        } else {
            $newShips = [];
            $lostShips = [];
            $lostAll = true;

            foreach (FleetsLib::getFleetShipsArray($fleet['fleet_array']) as $ship => $amount) {
                if (floor($amount * $lostChances) != 0) {
                    $lostShips[$ship] = floor($amount * $lostChances);
                    $newShips[$ship] = ($amount - $lostShips[$ship]);
                    $lostAll = false;
                }
            }

            if (!$lostAll) {
                $this->expeditionMessage(
                    $fleet['fleet_owner'],
                    __('game/expedition.exp_lost_1'),
                    (int) $fleet['fleet_end_stay'],
                    [
                        'galaxy' => $fleet['fleet_end_galaxy'],
                        'system' => $fleet['fleet_end_system'],
                        'planet' => $fleet['fleet_end_planet'],
                    ]
                );

                $this->updateLostShipsAndDefensePoints($fleet['fleet_owner'], $lostShips);
                $this->updateFleetArrayById([
                    'ships' => FleetsLib::setFleetShipsArray($newShips),
                    'fleet_id' => $fleet['fleet_id'],
                ]);
            } else {
                $this->expeditionMessage(
                    $fleet['fleet_owner'],
                    __('game/expedition.exp_lost_1'),
                    (int) $fleet['fleet_end_stay'],
                    [
                        'galaxy' => $fleet['fleet_end_galaxy'],
                        'system' => $fleet['fleet_end_system'],
                        'planet' => $fleet['fleet_end_planet'],
                    ]
                );

                $this->updateLostShipsAndDefensePoints(
                    $fleet['fleet_owner'],
                    FleetsLib::getFleetShipsArray($fleet['fleet_array'])
                );
                parent::removeFleet($fleet['fleet_id']);
            }
        }
    }

    private function resultNothing(array $fleet): void
    {
        $this->expeditionMessage(
            $fleet['fleet_owner'],
            __('game/expedition.exp_nothing_' . mt_rand(1, 6)),
            (int) $fleet['fleet_end_stay'],
            [
                'galaxy' => $fleet['fleet_end_galaxy'],
                'system' => $fleet['fleet_end_system'],
                'planet' => $fleet['fleet_end_planet'],
            ]
        );

        parent::returnFleet($fleet['fleet_id']);
    }

    private function expeditionMessage(int $owner, string $message, int $time, array $coords): void
    {
        $subject = sprintf(
            __('game/expedition.exp_report_title'),
            $this->formatService->prettyCoords((int) $coords['galaxy'], (int) $coords['system'], (int) $coords['planet'])
        );

        Functions::sendMessage(
            $owner,
            0,
            $time,
            5,
            __('game/missions.mi_fleet_command'),
            $subject,
            $message
        );
    }
}
