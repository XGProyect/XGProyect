<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Missions;

use App\Services\FormatService;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\FleetsLib as Fleets;
use Xgp\App\Libraries\Functions;

class Transport extends Missions
{
    public function __construct(private FormatService $formatService)
    {
        parent::__construct();
    }

    /**
     * Transport mission - deliver resources between planets
     *
     * @param array $fleet
     *
     * @return void
     */
    public function transportMission(array $fleet): void
    {
        // get required data
        $trading_planets = $this->getTradingPlanetsData($fleet);

        // do mission
        if (parent::canStartMission($fleet)) {
            // messages
            $this->sendDeliveryMessageToOwner($fleet, $trading_planets);
            $this->sendDeliveryMessageToReceiver($fleet, $trading_planets);

            // transfer the fleet resources to the planet
            parent::storeResources($fleet, false);
            $this->missionsModel->updateReturningFleetResources((int) $fleet['fleet_id']);

            // we need to remove the resources in case we are continuing with the
            // canCompleteMission instead of just ending here
            $fleet['fleet_resource_metal'] = 0;
            $fleet['fleet_resource_crystal'] = 0;
            $fleet['fleet_resource_deuterium'] = 0;
        }

        // complete mission
        if (parent::canCompleteMission($fleet)) {
            // message
            $this->sendReturnMessage($fleet, $trading_planets);

            // transfer the ships to the planet
            parent::restoreFleet($fleet);
            parent::removeFleet($fleet['fleet_id']);
        }
    }

    /**
     * Get data for the planets that are trading resources
     *
     * @param array $fleet
     *
     * @return array
     */
    private function getTradingPlanetsData(array $fleet): array
    {
        return $this->missionsModel->getFriendlyPlanetData([
            'coords' => [
                'start' => [
                    'galaxy' => $fleet['fleet_start_galaxy'],
                    'system' => $fleet['fleet_start_system'],
                    'planet' => $fleet['fleet_start_planet'],
                    'type' => $fleet['fleet_start_type'],
                ],
                'end' => [
                    'galaxy' => $fleet['fleet_end_galaxy'],
                    'system' => $fleet['fleet_end_system'],
                    'planet' => $fleet['fleet_end_planet'],
                    'type' => $fleet['fleet_end_type'],
                ],
            ],
        ]);
    }

    /**
     * Send a delivery message to the fleet owner
     *
     * @param array $fleet
     * @param array $trading_planets
     *
     * @return void
     */
    private function sendDeliveryMessageToOwner(array $fleet, array $trading_planets): void
    {
        // send message
        Functions::sendMessage(
            $trading_planets['start_id'],
            0,
            $fleet['fleet_start_time'],
            5,
            __('game/missions.mi_fleet_command'),
            __('game/transport.tra_reaching'),
            StringsHelper::parseReplacements(__('game/transport.tra_delivered_resources'), [
                $trading_planets['start_name'],
                Fleets::startLink($fleet, ''),
                $trading_planets['target_name'],
                Fleets::targetLink($fleet, ''),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_metal']),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_crystal']),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_deuterium']),
            ])
        );
    }

    /**
     * Send a delivery message to the receiver, only if the target planet is not a planet from the same user
     *
     * @param array $fleet
     * @param array $trading_planets
     *
     * @return void
     */
    private function sendDeliveryMessageToReceiver(array $fleet, array $trading_planets): void
    {
        if ($trading_planets['start_id'] != $trading_planets['target_id']) {
            // send message
            Functions::sendMessage(
                $trading_planets['target_id'],
                0,
                $fleet['fleet_start_time'],
                5,
                __('game/transport.tra_incoming_from'),
                __('game/transport.tra_incoming_title'),
                StringsHelper::parseReplacements(__('game/transport.tra_incoming_delivery'), [
                    $trading_planets['start_user_name'],
                    $trading_planets['start_name'],
                    Fleets::startLink($fleet, ''),
                    $trading_planets['target_name'],
                    Fleets::targetLink($fleet, ''),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_metal']),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_crystal']),
                    $this->formatService->prettyNumber((int) $fleet['fleet_resource_deuterium']),
                    $this->formatService->prettyNumber((int) $trading_planets['target_metal']),
                    $this->formatService->prettyNumber((int) $trading_planets['target_crystal']),
                    $this->formatService->prettyNumber((int) $trading_planets['target_deuterium']),
                    $this->formatService->prettyNumber($trading_planets['target_metal'] + $fleet['fleet_resource_metal']),
                    $this->formatService->prettyNumber($trading_planets['target_crystal'] + $fleet['fleet_resource_crystal']),
                    $this->formatService->prettyNumber($trading_planets['target_deuterium'] + $fleet['fleet_resource_deuterium']),
                ])
            );
        }
    }

    /**
     * Send a message informing that the fleet is back
     *
     * @param array $fleet
     * @param array $trading_planets
     *
     * @return void
     */
    private function sendReturnMessage(array $fleet, array $trading_planets): void
    {
        $text = __('game/missions.mi_fleet_back_without_resources');
        $replacements = [
            $trading_planets['target_name'],
            Fleets::targetLink($fleet, ''),
            $trading_planets['start_name'],
            Fleets::startLink($fleet, ''),
        ];

        if (Fleets::hasResources($fleet)) {
            $text = __('game/missions.mi_fleet_back_with_resources');
            $replacements = [
                $fleet['planet_end_name'],
                Fleets::targetLink($fleet, ''),
                $fleet['planet_start_name'],
                Fleets::startLink($fleet, ''),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_metal']),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_crystal']),
                $this->formatService->prettyNumber((int) $fleet['fleet_resource_deuterium']),
            ];
        }

        // send message
        Functions::sendMessage(
            $trading_planets['start_id'],
            0,
            $fleet['fleet_end_time'],
            5,
            __('game/missions.mi_fleet_command'),
            __('game/missions.mi_fleet_back_title'),
            StringsHelper::parseReplacements($text, $replacements)
        );
    }
}
