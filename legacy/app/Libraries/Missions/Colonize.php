<?php

namespace Xgp\App\Libraries\Missions;

use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\PlanetLib;
use Xgp\App\Libraries\StatisticsLibrary;

class Colonize extends Missions
{
    public function __construct()
    {
        parent::__construct();
    }

    public function colonizeMission($fleet_row): void
    {
        if ($fleet_row['fleet_mess'] == 0) {
            $colonization_check = $this->missionsModel->getPlanetAndUserCountsCounts([
                'user_id' => $fleet_row['fleet_owner'],
                'coords' => [
                    'galaxy' => $fleet_row['fleet_end_galaxy'],
                    'system' => $fleet_row['fleet_end_system'],
                    'planet' => $fleet_row['fleet_end_planet'],
                ],
            ]);

            // some required values
            $target_coords = sprintf(__('game/missions.mi_planet_coordinates'), $fleet_row['fleet_end_galaxy'], $fleet_row['fleet_end_system'], $fleet_row['fleet_end_planet']);
            $max_colonies = FleetsLib::getMaxColonies($colonization_check['astro_level']);
            $planet_count = $colonization_check['planet_count'] - 1; // the total amount of planets minus 1 (because the main planet is not considered)

            // different types of messages
            $message[1] = sprintf(__('game/colonize.col_max_colonies'), $target_coords, ($max_colonies + 1));
            $message[2] = sprintf(__('game/colonize.col_successful'), $target_coords);
            $message[3] = sprintf(__('game/colonize.col_occupied'), $target_coords);
            $message[4] = sprintf(__('game/colonize.col_astro_level'), $target_coords);

            if ($colonization_check['galaxy_count'] == 0) {
                if ($planet_count >= $max_colonies) {
                    $this->colonizeMessage($fleet_row['fleet_owner'], $message[1], $fleet_row['fleet_start_time']);

                    parent::returnFleet($fleet_row['fleet_id']);
                } elseif (!$this->positionAllowed($fleet_row['fleet_end_planet'], $colonization_check['astro_level'])) {
                    $this->colonizeMessage($fleet_row['fleet_owner'], $message[4], $fleet_row['fleet_start_time']);

                    parent::returnFleet($fleet_row['fleet_id']);
                } else {
                    if ($this->startCreation($fleet_row)) {
                        $this->colonizeMessage($fleet_row['fleet_owner'], $message[2], $fleet_row['fleet_start_time']);

                        if ($fleet_row['fleet_amount'] == 1) {
                            $this->missionsModel->updateColonizationStatistics([
                                'points' => StatisticsLibrary::calculatePoints(208, 1),
                                'coords' => [
                                    'galaxy' => $fleet_row['fleet_start_galaxy'],
                                    'system' => $fleet_row['fleet_start_system'],
                                    'planet' => $fleet_row['fleet_start_planet'],
                                    'type' => $fleet_row['fleet_start_type'],
                                ],
                            ]);
                            parent::storeResources($fleet_row);
                            parent::removeFleet($fleet_row['fleet_id']);
                        } else {
                            parent::storeResources($fleet_row);

                            $this->missionsModel->updateColonizatonReturningFleet([
                                'ships' => $this->buildNewFleet($fleet_row['fleet_array']),
                                'points' => StatisticsLibrary::calculatePoints(208, 1),
                                'fleet_id' => $fleet_row['fleet_id'],
                                'coords' => [
                                    'galaxy' => $fleet_row['fleet_start_galaxy'],
                                    'system' => $fleet_row['fleet_start_system'],
                                    'planet' => $fleet_row['fleet_start_planet'],
                                    'type' => $fleet_row['fleet_start_type'],
                                ],
                            ]);
                        }
                    } else {
                        $this->colonizeMessage($fleet_row['fleet_owner'], $message[3], $fleet_row['fleet_end_time']);

                        parent::returnFleet($fleet_row['fleet_id']);
                    }
                }
            } else {
                $this->colonizeMessage($fleet_row['fleet_owner'], $message[3], $fleet_row['fleet_end_time']);

                parent::returnFleet($fleet_row['fleet_id']);
            }
        }

        if ($fleet_row['fleet_end_time'] < time()) {
            parent::restoreFleet($fleet_row, true);
            parent::removeFleet($fleet_row['fleet_id']);
        }
    }

    private function startCreation($fleet_row)
    {
        $creator = new PlanetLib();

        return $creator->setNewPlanet($fleet_row['fleet_end_galaxy'], $fleet_row['fleet_end_system'], $fleet_row['fleet_end_planet'], $fleet_row['fleet_owner']);
    }

    private function buildNewFleet(string $fleetArray): bool
    {
        $current_fleet = FleetsLib::getFleetShipsArray($fleetArray);
        $new_fleet = [];

        foreach ($current_fleet as $ship => $count) {
            if ($ship == 208) {
                if ($count > 1) {
                    $new_fleet[$ship] = ($count - 1);
                }
            } else {
                if ($count != 0) {
                    $new_fleet[$ship] = $count;
                }
            }
        }

        return FleetsLib::setFleetShipsArray($new_fleet);
    }

    /**
     * Send colonization message
     *
     * @param int $owner
     * @param string $message
     * @param int $time
     * @return void
     */
    private function colonizeMessage($owner, $message, $time)
    {
        Functions::sendMessage($owner, '', $time, 5, __('game/colonize.col_report_from'), __('game/colonize.col_report_title'), $message);
    }

    /**
     * Check if position is allowed
     *
     * @param int $position
     * @param int $level
     * @return bool
     */
    private function positionAllowed(int $position, int $level): bool
    {
        // CHECK IF THE POSITION IS NEAR THE SPACE LIMITS
        if ($position <= 3 or $position >= 13) {
            // POSITIONS 3 AND 13 CAN BE POPULATED FROM LEVEL 4 ONWARDS.
            if ($level >= 4 && ($position == 3 or $position == 13)) {
                return true;
            }

            // POSITIONS 2 AND 14 CAN BE POPULATED FROM LEVEL 6 ONWARDS.
            if ($level >= 6 && ($position == 2 or $position == 14)) {
                return true;
            }

            // POSITIONS 1 AND 15 CAN BE POPULATED FROM LEVEL 8 ONWARDS.
            if ($level >= 8) {
                return true;
            }

            return false;
        } else {
            return true;
        }
    }
}
