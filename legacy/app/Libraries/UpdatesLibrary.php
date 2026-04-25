<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use App\Services\Admin\BackupService;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\BuildingsEnumerator as Buildings;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Enumerators\ResearchEnumerator as Research;
use Xgp\App\Core\Objects;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\DevelopmentsLib as Developments;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 * @SuppressWarnings("PHPMD.UnusedLocalVariable")
 */
class UpdatesLibrary
{
    use PreparesLegacySql;

    public function __construct(private ProductionService $productionService)
    {

        // Other stuff
        $this->cleanUp();
        $this->createBackup();

        // Updates
        $this->updateFleets();
        $this->updateStatistics();
    }

    private function cleanUp(): void
    {
        $settings = app(SettingsService::class);
        $lastCleanup = $settings->getInt('last_cleanup');
        $cleanupInterval = 6; // 6 HOURS

        if ((time() >= ($lastCleanup + (3600 * $cleanupInterval)))) {
            // TIMERS
            $delPlanets = time() - ONE_DAY;
            $delBefore = time() - ONE_WEEK;
            $delInactive = time() - ONE_MONTH;
            $delDeleted = time() - ONE_WEEK;

            // USERS TO DELETE
            $chooseToDelete = array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT u.`id`
                        FROM `' . USERS . '` AS u
                        INNER JOIN `' . PREFERENCES . "` AS p ON p.preference_user_id = u.id
                        WHERE (p.`preference_delete_mode` < '" . $delDeleted . "'
                            AND p.`preference_delete_mode` <> 0)
                            OR (u.`onlinetime` < '" . $delInactive . "' AND u.`onlinetime` <> 0 AND u.`authlevel` <> 3)"
                    )
                )
            );

            $users = new Users();

            if ($chooseToDelete) {
                foreach ($chooseToDelete as $delete) {
                    $users->deleteUser((int) $delete['id']);
                }
            }

            // Misc deletions
            DB::statement($this->prepareSql('DELETE FROM ' . MESSAGES . " WHERE `message_time` < '" . $delBefore . "';"));
            DB::statement($this->prepareSql('DELETE FROM ' . REPORTS . " WHERE `report_time` < '" . $delBefore . "';"));
            DB::table('sessions')->where('last_activity', '<', $delPlanets)->delete();
            DB::statement(
                $this->prepareSql(
                    'DELETE p,b,d,s FROM `' . PLANETS . '` AS p
                    INNER JOIN `' . BUILDINGS . '` AS b ON b.building_planet_id = p.`planet_id`
                    INNER JOIN `' . DEFENSES . '` AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN `' . SHIPS . "` AS s ON s.ship_planet_id = p.`planet_id`
                    WHERE `planet_destroyed` < '" . $delPlanets . "'
                        AND `planet_destroyed` <> 0;"
                )
            );
            DB::statement(
                $this->prepareSql(
                    'DELETE a,m1,m2 FROM `' . ACS . '` AS a
                    INNER JOIN `' . ACS_MEMBERS . '` m1 ON m1.`acs_group_id` = a.`acs_id`
                    RIGHT JOIN `' . ACS_MEMBERS . '` m2 ON m2.`acs_group_id` = a.`acs_id`
                    LEFT JOIN `' . FLEETS . '` f ON f.`fleet_group` = a.`acs_id`
                    WHERE f.`fleet_id` IS NULL'
                )
            );

            $settings->write('last_cleanup', time());
        }
    }

    private function createBackup(): void
    {
        $settings = app(SettingsService::class);
        $autoBackup = $settings->getBool('auto_backup');
        $lastBackup = $settings->getInt('last_backup');
        $updateInterval = 6;

        if ((time() >= ($lastBackup + (3600 * $updateInterval))) && $autoBackup) {
            app(BackupService::class)->createBackup();

            $settings->write('last_backup', time());
        }
    }

    /**
     * updateBuildingsQueue
     *
     * @param array $current_planet Current planet
     * @param array $current_user   Current user
     *
     * @return void
     */
    public static function updateBuildingsQueue(&$current_planet, &$current_user)
    {
        while ($current_planet['planet_b_building_id'] != 0) {
            if ($current_planet['planet_b_building'] <= time()) {
                if (self::checkBuildingQueue($current_planet, $current_user)) {
                    self::setFirstElement($current_planet, $current_user);
                } else {
                    break;
                }
            } else {
                break;
            }
        }
    }

    /**
     * updateFleets
     *
     * @return void
     */
    private function updateFleets()
    {
        // let's start the missions control process
        $mission_control = new MissionControlLib();
        $mission_control->arrivingFleets();
        $mission_control->returningFleets();
    }

    /**
     * updateStatistics
     *
     * @return void
     */
    private function updateStatistics()
    {
        // LAST UPDATE AND UPDATE INTERVAL, EX: 15 MINUTES
        $settings = app(SettingsService::class);
        $stat_last_update = $settings->getInt('stat_last_update');
        $update_interval = $settings->getInt('stat_update_time');

        if ((time() >= ($stat_last_update + (60 * $update_interval)))) {
            $result = new StatisticsLibrary();

            $settings->write('stat_last_update', $result->makeStats()['stats_time']);
        }
    }

    /**
     * Check the current queue, remove the first element and update the planet with what was just completed
     *
     * @param array $current_planet
     * @param array $current_user
     *
     * @return boolean
     */
    private static function sql(string $sql): string
    {
        return strtr($sql, ['{xgp_prefix}' => DB::getTablePrefix()]);
    }

    private static function checkBuildingQueue(&$current_planet, &$current_user): bool
    {
        $resource = Objects::getInstance()->getObjects();
        $ret_value = false;
        $queue_array = [];

        if (!empty($current_planet['planet_b_building_id'])) {
            $current_queue = $current_planet['planet_b_building_id'];

            if ($current_queue != 0) {
                $queue_array = explode(';', $current_queue);
            }

            $build_array = explode(',', $queue_array[0]);
            $element = $build_array[0];
            $build_end_time = floor((int) $build_array[3]);
            $build_mode = $build_array[4];

            array_shift($queue_array);

            $for_destroy = ($build_mode == 'destroy') ? true : false;

            if ($build_end_time <= time()) {
                $current = (int) $current_planet['planet_field_current'];
                $max = (int) $current_planet['planet_field_max'];

                if ($element == Buildings::BUILDING_MONDBASIS) {
                    $current += 1;
                    $max += FIELDS_BY_MOONBASIS_LEVEL;
                    $current_planet[$resource[$element]]++;
                } else {
                    if ($for_destroy == false) {
                        $current += 1;
                        $current_planet[$resource[$element]]++;
                    } else {
                        $current -= 1;
                        $current_planet[$resource[$element]]--;
                    }
                }

                $new_queue = (count($queue_array) == 0) ? 0 : join(';', $queue_array);

                $current_planet['planet_b_building'] = 0;
                $current_planet['planet_b_building_id'] = $new_queue;
                $current_planet['planet_field_current'] = $current;
                $current_planet['planet_field_max'] = $max;
                $current_planet['building_points'] = StatisticsLibrary::calculatePoints(
                    $element,
                    $current_planet[$resource[$element]]
                );

                DB::statement(
                    self::sql(
                        'UPDATE ' . PLANETS . ' AS p
                        INNER JOIN ' . USERS_STATISTICS . ' AS s ON s.user_statistic_user_id = p.planet_user_id
                        INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id` SET
                        `' . $resource[$element] . "` = '" . $current_planet[$resource[$element]] . "',
                        `user_statistic_buildings_points` = `user_statistic_buildings_points` + '" .
                        $current_planet['building_points'] . "',
                        `planet_b_building` = '" . $current_planet['planet_b_building'] . "',
                        `planet_b_building_id` = '" . $current_planet['planet_b_building_id'] . "',
                        `planet_field_current` = '" . $current_planet['planet_field_current'] . "',
                        `planet_field_max` = '" . $current_planet['planet_field_max'] . "'
                        WHERE `planet_id` = '" . $current_planet['planet_id'] . "';"
                    )
                );

                $ret_value = true;
            } else {
                $ret_value = false;
            }
        } else {
            $current_planet['planet_b_building'] = 0;
            $current_planet['planet_b_building_id'] = 0;

            DB::statement(
                self::sql(
                    'UPDATE ' . PLANETS . " SET
                    `planet_b_building` = '" . $current_planet['planet_b_building'] . "',
                    `planet_b_building_id` = '" . $current_planet['planet_b_building_id'] . "'
                    WHERE `planet_id` = '" . $current_planet['planet_id'] . "';"
                )
            );

            $ret_value = false;
        }

        return $ret_value;
    }

    /**
     * Set the next element in the queue to be the first
     *
     * @param array $current_planet
     * @param array $current_user
     *
     * @return void
     */
    public static function setFirstElement(&$current_planet, $current_user): void
    {
        $resource = Objects::getInstance()->getObjects();
        $devService = app(DevelopmentsService::class);

        if ($current_planet['planet_b_building'] == 0) {
            $current_queue = $current_planet['planet_b_building_id'];
            $build_end_time = '0';
            $new_queue = '0';

            if ($current_queue != 0) {
                $queue_array = explode(';', $current_queue);
                $loop = true;

                while ($loop) {
                    $list_id_array = explode(',', $queue_array[0]);
                    $element = (int) $list_id_array[0];
                    $level = (int) $list_id_array[1];
                    $build_time = $list_id_array[2];
                    $build_end_time = $list_id_array[3];
                    $build_mode = $list_id_array[4];
                    $no_more_level = false;

                    $for_destroy = ($build_mode == 'destroy') ? true : false;

                    $ionTechLevel = $for_destroy ? (int) ($current_user[$resource[Research::research_ionic_technology]] ?? 0) : 0;

                    $is_payable = $devService->isDevelopmentPayable(
                        $current_planet,
                        $element,
                        (int) ($current_planet[$resource[$element]] ?? 0),
                        true,
                        $for_destroy,
                        $ionTechLevel
                    );

                    if ($for_destroy) {
                        if ($current_planet[$resource[$element]] == 0) {
                            $is_payable = false;
                            $no_more_level = true;
                        }
                    }

                    if ($is_payable) {
                        $price = $devService->developmentPrice(
                            $element,
                            (int) ($current_planet[$resource[$element]] ?? 0),
                            true,
                            $for_destroy,
                            $ionTechLevel
                        );
                        $recalculated_queue = [];

                        $current_planet['planet_metal'] -= $price['metal'] ?? 0;
                        $current_planet['planet_crystal'] -= $price['crystal'] ?? 0;
                        $current_planet['planet_deuterium'] -= $price['deuterium'] ?? 0;

                        $prevData = 0;

                        // if we upgrade robots or nanobots we must recalculate everything
                        foreach ($queue_array as $queue_item => $data) {
                            $element_data = explode(',', $data);
                            $previous_time = $element_data[2];
                            $element_data[2] = $devService->developmentTime(
                                (int) $element_data[0],
                                (int) ($current_planet[$resource[(int) $element_data[0]]] ?? 0),
                                (int) $current_planet[$resource[Buildings::BUILDING_ROBOT_FACTORY]],
                                (int) $current_planet[$resource[Buildings::BUILDING_NANO_FACTORY]],
                                0,
                                0,
                                false
                            );
                            if ($for_destroy) {
                                $element_data[2] = $devService->tearDownTime(
                                    (int) $element_data[0],
                                    (int) ($current_planet[$resource[(int) $element_data[0]]] ?? 0),
                                    (int) $current_planet[$resource[Buildings::BUILDING_ROBOT_FACTORY]],
                                    (int) $current_planet[$resource[Buildings::BUILDING_NANO_FACTORY]]
                                );
                            }

                            if ($prevData == 0) {
                                // remove the previous building time and add the new building time
                                $element_data[3] = $element_data[3] - $previous_time + $element_data[2];

                                // for planet_b_building, set the first queue element completion time
                                $build_end_time = $element_data[3];
                            } else {
                                $element_data[3] = $prevData + $element_data[2];
                            }

                            $prevData = $element_data[3];

                            $recalculated_queue[$queue_item] = join(',', $element_data);
                        }

                        $new_queue = join(';', $recalculated_queue);

                        if ($new_queue == '') {
                            $new_queue = '0';
                        }

                        $loop = false;
                    } else {
                        $element_name = __('game/constructions.' . $resource[$element]);

                        if ($no_more_level == true) {
                            $message = '';
                        } else {
                            $price = Developments::developmentPrice(
                                $current_user,
                                $current_planet,
                                (int) $element,
                                true,
                                $for_destroy
                            );

                            $insufficient = [];

                            if (($price['metal'] ?? 0) > $current_planet['planet_metal']) {
                                $insufficient[] = __('game/global.metal');
                            }

                            if (($price['crystal'] ?? 0) > $current_planet['planet_crystal']) {
                                $insufficient[] = __('game/global.crystal');
                            }

                            if (($price['deuterium'] ?? 0) > $current_planet['planet_deuterium']) {
                                $insufficient[] = __('game/global.deuterium');
                            }

                            $message = sprintf(
                                __('game/buildings.bd_building_queue_not_enough_resources'),
                                __('game/buildings.bd_building_queue_' . $build_mode . '_order'),
                                $element_name,
                                $level,
                                UrlHelper::setUrl(
                                    'game.php?page=galaxy&mode=3&galaxy=' . $current_planet['planet_galaxy'] . '&system=' . $current_planet['planet_system'],
                                    $current_planet['planet_name'] . ' ' . app(FormatService::class)->prettyCoords(
                                        (int) $current_planet['planet_galaxy'],
                                        (int) $current_planet['planet_system'],
                                        (int) $current_planet['planet_planet']
                                    )
                                ),
                                join(', ', $insufficient)
                            );
                        }

                        if ($message != '') {
                            Functions::sendMessage(
                                $current_user['id'],
                                0,
                                0,
                                5,
                                __('game/buildings.bd_building_queue_not_enough_resources_from'),
                                __('game/buildings.bd_building_queue_not_enough_resources_subject'),
                                $message,
                                true
                            );
                        }

                        array_shift($queue_array);

                        foreach ($queue_array as $num => $info) {
                            $fix_ele = explode(',', $info);
                            $fix_ele[3] = $fix_ele[3] - $build_time; // build end time
                            $queue_array[$num] = join(',', $fix_ele);
                        }

                        $actual_count = count($queue_array);

                        if ($actual_count == 0) {
                            $build_end_time = '0';
                            $new_queue = '0';
                            $loop = false;
                        }
                    }
                }
            }

            $current_planet['planet_b_building'] = $build_end_time;
            $current_planet['planet_b_building_id'] = $new_queue;

            DB::statement(
                self::sql(
                    'UPDATE `' . PLANETS . "` SET
                        `planet_metal` = '" . $current_planet['planet_metal'] . "',
                        `planet_crystal` = '" . $current_planet['planet_crystal'] . "',
                        `planet_deuterium` = '" . $current_planet['planet_deuterium'] . "',
                        `planet_b_building` = '" . $current_planet['planet_b_building'] . "',
                        `planet_b_building_id` = '" . $current_planet['planet_b_building_id'] . "'
                    WHERE `planet_id` = '" . $current_planet['planet_id'] . "';"
                )
            );
        }
    }

    /**
     * Update the planet resources
     *
     * @param array   $current_user   Current user
     * @param array   $current_planet Current planet
     * @param int     $UpdateTime     Update time
     * @param boolean $Simul          Simulation
     *
     * @return void
     */
    public static function updatePlanetResources(&$current_user, &$current_planet, $UpdateTime, $Simul = false)
    {
        $resource = Objects::getInstance()->getObjects();
        $ProdGrid = Objects::getInstance()->getProduction();

        $settings = app(SettingsService::class);
        $productionService = app(ProductionService::class); // Get service from container
        $officerService = app(OfficerService::class);
        $game_resource_multiplier = $settings->getInt('resource_multiplier');
        $game_metal_basic_income = $settings->getInt('metal_basic_income');
        $game_crystal_basic_income = $settings->getInt('crystal_basic_income');
        $game_deuterium_basic_income = $settings->getInt('deuterium_basic_income');

        if ($current_user['preference_vacation_mode'] > 0) {
            $game_metal_basic_income = 0;
            $game_crystal_basic_income = 0;
            $game_deuterium_basic_income = 0;
        }

        $current_planet['planet_metal_max'] = $productionService->maxStorable((int) $current_planet[$resource[22]]);
        $current_planet['planet_crystal_max'] = $productionService->maxStorable((int) $current_planet[$resource[23]]);
        $current_planet['planet_deuterium_max'] = $productionService->maxStorable((int) $current_planet[$resource[24]]);

        $MaxMetalStorage = $current_planet['planet_metal_max'];
        $MaxCristalStorage = $current_planet['planet_crystal_max'];
        $MaxDeuteriumStorage = $current_planet['planet_deuterium_max'];

        $Caps = [];
        $BuildTemp = $current_planet['planet_temp_max'];
        $sub_query = '';
        $parse['production_level'] = 100;

        $post_percent = $productionService->maxProductionPercentage(
            (int) $current_planet['planet_energy_max'],
            (int) $current_planet['planet_energy_used']
        );

        $Caps['planet_metal_perhour'] = 0;
        $Caps['planet_crystal_perhour'] = 0;
        $Caps['planet_deuterium_perhour'] = 0;
        $Caps['planet_energy_max'] = 0;
        $Caps['planet_energy_used'] = 0;

        foreach ($ProdGrid as $ProdID => $formula) {
            $BuildLevelFactor = $current_planet['planet_' . $resource[$ProdID] . '_percent'];
            $BuildLevel = $current_planet[$resource[$ProdID]];
            $BuildEnergy = $current_user['research_energy_technology'];

            // BOOST
            $geologe_boost = 1 + (1 * ($officerService->isOfficerActive(
                (int) $current_user['premium_officier_geologist'],
                time()
            ) ? GEOLOGUE : 0));
            $engineer_boost = 1 + (1 * ($officerService->isOfficerActive(
                (int) $current_user['premium_officier_engineer'],
                time()
            ) ? ENGINEER_ENERGY : 0));

            // PRODUCTION FORMULAS
            $metal_prod = ($formula['formule']['metal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $crystal_prod = ($formula['formule']['crystal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $deuterium_prod = ($formula['formule']['deuterium'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $energy_prod = ($formula['formule']['energy'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);

            // PLASMA BOOST
            $metalBoost = Formulas::getPlasmaTechnologyBonus((int) $current_user['research_plasma_technology'], 'metal');
            $crystalBoost = Formulas::getPlasmaTechnologyBonus((int) $current_user['research_plasma_technology'], 'crystal');
            $deuteriumBoost = Formulas::getPlasmaTechnologyBonus((int) $current_user['research_plasma_technology'], 'deuterium');

            // PRODUCTION BOOST WITH OFFICERS
            $Caps['planet_metal_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($metal_prod, $geologe_boost, $game_resource_multiplier),
                $post_percent
            );

            $Caps['planet_crystal_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($crystal_prod, $geologe_boost, $game_resource_multiplier),
                $post_percent
            );

            $Caps['planet_deuterium_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($deuterium_prod, $geologe_boost, $game_resource_multiplier),
                $post_percent
            );

            // PRODUCTION BOOST WITH PLASMA
            $Caps['planet_metal_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($metal_prod, $metalBoost, $game_resource_multiplier),
                $post_percent
            );

            $Caps['planet_crystal_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($crystal_prod, $crystalBoost, $game_resource_multiplier),
                $post_percent
            );

            $Caps['planet_deuterium_perhour'] += $productionService->currentProduction(
                $productionService->productionAmount($deuterium_prod, $deuteriumBoost, $game_resource_multiplier),
                $post_percent
            );

            if ($ProdID >= 4) {
                if ($ProdID == 12 && $current_planet['planet_deuterium'] == 0) {
                    continue;
                }

                $Caps['planet_energy_max'] += $productionService->productionAmount(
                    $energy_prod,
                    $engineer_boost,
                    0,
                    true
                );
            } else {
                $Caps['planet_energy_used'] += $productionService->productionAmount(
                    $energy_prod,
                    1,
                    0,
                    true
                );
            }
        }

        if ($current_planet['planet_type'] == PlanetTypesEnumerator::MOON) {
            $game_metal_basic_income = 0;
            $game_crystal_basic_income = 0;
            $game_deuterium_basic_income = 0;
            $current_planet['planet_metal_perhour'] = 0;
            $current_planet['planet_crystal_perhour'] = 0;
            $current_planet['planet_deuterium_perhour'] = 0;
            $current_planet['planet_energy_used'] = 0;
            $current_planet['planet_energy_max'] = 0;
        } else {
            $current_planet['planet_metal_perhour'] = $Caps['planet_metal_perhour'] + $game_metal_basic_income;
            $current_planet['planet_crystal_perhour'] = $Caps['planet_crystal_perhour'] + $game_crystal_basic_income;
            $current_planet['planet_deuterium_perhour'] = $Caps['planet_deuterium_perhour'] + $game_deuterium_basic_income;
            $current_planet['planet_energy_used'] = $Caps['planet_energy_used'];
            $current_planet['planet_energy_max'] = $Caps['planet_energy_max'];
        }

        $ProductionTime = ($UpdateTime - $current_planet['planet_last_update']);
        $current_planet['planet_last_update'] = $UpdateTime;

        if ($current_planet['planet_energy_max'] == 0) {
            $current_planet['planet_metal_perhour'] = $game_metal_basic_income;
            $current_planet['planet_crystal_perhour'] = $game_crystal_basic_income;
            $current_planet['planet_deuterium_perhour'] = $game_deuterium_basic_income;

            $production_level = 100;
        } elseif ($current_planet['planet_energy_max'] >= $current_planet['planet_energy_used']) {
            $production_level = 100;
        } else {
            $production_level = floor(
                ((float) $current_planet['planet_energy_max'] / (float) $current_planet['planet_energy_used']) * 100
            );
        }

        if ($production_level > 100) {
            $production_level = 100;
        } elseif ($production_level < 0) {
            $production_level = 0;
        }

        if ($current_planet['planet_metal'] <= $MaxMetalStorage) {
            $MetalProduction = (
                ($ProductionTime * ($current_planet['planet_metal_perhour'] / 3600))
            ) * (0.01 * $production_level);

            $MetalBaseProduc = (($ProductionTime * ($game_metal_basic_income / 3600)));
            $MetalTheorical = $current_planet['planet_metal'] + $MetalProduction + $MetalBaseProduc;

            if ($MetalTheorical <= $MaxMetalStorage) {
                $current_planet['planet_metal'] = $MetalTheorical;
            } else {
                $current_planet['planet_metal'] = $MaxMetalStorage;
            }
        }

        if ($current_planet['planet_crystal'] <= $MaxCristalStorage) {
            $CristalProduction = (
                ($ProductionTime * ($current_planet['planet_crystal_perhour'] / 3600))
            ) * (0.01 * $production_level);

            $CristalBaseProduc = (($ProductionTime * ($game_crystal_basic_income / 3600)));
            $CristalTheorical = $current_planet['planet_crystal'] + $CristalProduction + $CristalBaseProduc;

            if ($CristalTheorical <= $MaxCristalStorage) {
                $current_planet['planet_crystal'] = $CristalTheorical;
            } else {
                $current_planet['planet_crystal'] = $MaxCristalStorage;
            }
        }

        if ($current_planet['planet_deuterium'] <= $MaxDeuteriumStorage) {
            $DeuteriumProduction = (
                ($ProductionTime * ($current_planet['planet_deuterium_perhour'] / 3600))
            ) * (0.01 * $production_level);

            $DeuteriumBaseProduc = (($ProductionTime * ($game_deuterium_basic_income / 3600)));
            $DeuteriumTheorical = $current_planet['planet_deuterium'] +
                $DeuteriumProduction + $DeuteriumBaseProduc;

            if ($DeuteriumTheorical <= $MaxDeuteriumStorage) {
                $current_planet['planet_deuterium'] = $DeuteriumTheorical;
            } else {
                $current_planet['planet_deuterium'] = $MaxDeuteriumStorage;
            }
        }

        if ($current_planet['planet_metal'] < 0) {
            $current_planet['planet_metal'] = 0;
        }

        if ($current_planet['planet_crystal'] < 0) {
            $current_planet['planet_crystal'] = 0;
        }

        if ($current_planet['planet_deuterium'] < 0) {
            $current_planet['planet_deuterium'] = 0;
        }

        if ($Simul == false) {

            // SHIPS AND DEFENSES UPDATE
            $builded = self::updateHangarQueue($current_user, $current_planet, $ProductionTime);
            $ship_points = 0;
            $defense_points = 0;

            if ($builded != '') {
                foreach ($builded as $element => $count) {
                    if ($element != '') {
                        // POINTS
                        switch ($element) {
                            case (($element >= 202) && ($element <= 215)):
                                $ship_points += StatisticsLibrary::calculatePoints($element, $count) * $count;
                                break;
                            case (($element >= 401) && ($element <= 503)):
                                $defense_points += StatisticsLibrary::calculatePoints($element, $count) * $count;
                                break;
                            default:
                                break;
                        }

                        if ($resource[$element] != '') {
                            $sub_query .= '`' . $resource[$element] . "` = '" . $current_planet[$resource[$element]] . "', ";
                        }
                    }
                }
            }

            // RESEARCH UPDATE
            if ($current_planet['planet_b_tech'] <= time() && $current_planet['planet_b_tech_id'] != 0) {
                $current_user['research_points'] = StatisticsLibrary::calculatePoints(
                    $current_planet['planet_b_tech_id'],
                    $current_user[$resource[$current_planet['planet_b_tech_id']]],
                    'tech'
                );

                $current_user[$resource[$current_planet['planet_b_tech_id']]]++;

                $tech_query = "`planet_b_tech` = '0',";
                $tech_query .= "`planet_b_tech_id` = '0',";
                $tech_query .= '`' . $resource[$current_planet['planet_b_tech_id']] . "` = '" .
                    $current_user[$resource[$current_planet['planet_b_tech_id']]] . "',";
                $tech_query .= "`user_statistic_technology_points` = `user_statistic_technology_points` + '" .
                    $current_user['research_points'] . "',";
                $tech_query .= "`research_current_research` = '0',";
            } else {
                $tech_query = '';
            }

            $data = [
                'planet' => $current_planet,
                'ship_points' => $ship_points,
                'defense_points' => $defense_points,
                'sub_query' => $sub_query,
                'tech_query' => $tech_query,
            ];

            DB::statement(
                self::sql(
                    'UPDATE ' . PLANETS . ' AS p
                    INNER JOIN ' . USERS_STATISTICS . ' AS us ON us.user_statistic_user_id = p.planet_user_id
                    INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN ' . SHIPS . ' AS s ON s.ship_planet_id = p.`planet_id`
                    INNER JOIN ' . RESEARCH . " AS r ON r.research_user_id = p.planet_user_id SET
                        `planet_metal` = '" . $data['planet']['planet_metal'] . "',
                        `planet_crystal` = '" . $data['planet']['planet_crystal'] . "',
                        `planet_deuterium` = '" . $data['planet']['planet_deuterium'] . "',
                        `planet_last_update` = '" . $data['planet']['planet_last_update'] . "',
                        `planet_b_hangar_id` = '" . $data['planet']['planet_b_hangar_id'] . "',
                        `planet_metal_perhour` = '" . $data['planet']['planet_metal_perhour'] . "',
                        `planet_crystal_perhour` = '" . $data['planet']['planet_crystal_perhour'] . "',
                        `planet_deuterium_perhour` = '" . $data['planet']['planet_deuterium_perhour'] . "',
                        `planet_energy_used` = '" . $data['planet']['planet_energy_used'] . "',
                        `planet_energy_max` = '" . $data['planet']['planet_energy_max'] . "',
                        `user_statistic_ships_points` = `user_statistic_ships_points` + '" . $data['ship_points'] . "',
                        `user_statistic_defenses_points` = `user_statistic_defenses_points`  + '" . $data['defense_points'] . "',
                        {$data['sub_query']}
                        {$data['tech_query']}
                        `planet_b_hangar` = '" . $data['planet']['planet_b_hangar'] . "'
                    WHERE `planet_id` = '" . $data['planet']['planet_id'] . "';"
                )
            );
        }
    }

    /**
     * Update the hangar queue, ships and defenses that were on queue
     */
    private static function updateHangarQueue(array $current_user, array &$current_planet, int $ProductionTime): array
    {
        $resource = Objects::getInstance()->getObjects();

        if ($current_planet['planet_b_hangar_id'] != '') {
            $Builded = [];
            $BuildArray = [];
            $BuildQueue = explode(';', $current_planet['planet_b_hangar_id']);

            $current_planet['planet_b_hangar'] += $ProductionTime;

            foreach ($BuildQueue as $Node => $Array) {
                if ($Array != '') {
                    $Item = explode(',', $Array);

                    if (isset($Item[0]) && $Item[0] != 0) {
                        $AcumTime = Developments::developmentTime(
                            $current_user,
                            $current_planet,
                            (int) $Item[0]
                        );
                        $BuildArray[$Node] = [$Item[0], $Item[1], $AcumTime];
                    }
                }
            }

            $current_planet['planet_b_hangar_id'] = '';
            $UnFinished = false;

            foreach ($BuildArray as $Node => $Item) {
                $Element = $Item[0];
                $Count = $Item[1];
                $BuildTime = $Item[2];
                $Builded[$Element] = 0;

                if (!$UnFinished and $BuildTime > 0) {
                    $AllTime = $BuildTime * $Count;

                    if ($current_planet['planet_b_hangar'] >= $BuildTime) {
                        $Done = min($Count, floor((float) $current_planet['planet_b_hangar'] / $BuildTime));

                        if ($Count > $Done) {
                            $current_planet['planet_b_hangar'] -= $BuildTime * $Done;

                            $UnFinished = true;
                            $Count -= $Done;
                        } else {
                            $current_planet['planet_b_hangar'] -= $AllTime;
                            $Count = 0;
                        }

                        $Builded[$Element] += $Done;
                        $current_planet[$resource[$Element]] += $Done;
                    } else {
                        $UnFinished = true;
                    }
                } elseif (!$UnFinished) {
                    $Builded[$Element] += $Count;
                    $current_planet[$resource[$Element]] += $Count;
                    $Count = 0;
                }

                if ($Count != 0) {
                    $current_planet['planet_b_hangar_id'] .= $Element . ',' . $Count . ';';
                }
            }
        } else {
            $Builded = [];
            $current_planet['planet_b_hangar'] = 0;
        }

        return $Builded;
    }
}
