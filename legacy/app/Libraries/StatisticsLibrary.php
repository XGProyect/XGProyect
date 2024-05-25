<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Xgp\App\Core\Objects;
use Xgp\App\Core\Options;
use Xgp\App\Models\Libraries\StatisticsLibrary as StatisticsLib;

class StatisticsLibrary
{
    private StatisticsLib $statisticsLibraryModel;
    private $time;

    public function __construct()
    {
        $this->statisticsLibraryModel = new StatisticsLib();
    }

    /**
     * calculatePoints
     *
     * @param string $element Element
     * @param int    $level   Level
     * @param string $type    Type
     *
     * @return int
     */
    public static function calculatePoints($element, $level, $type = '')
    {
        switch ($type) {
            case 'tech':
                $current_level = $level;

                break;

            case '':
            default:
                $current_level = ($level - 1 < 0) ? 0 : $level - 1;

                break;
        }

        $element = Objects::getInstance()->getPrice($element);
        $resources_total = $element['metal'] + $element['crystal'] + $element['deuterium'];
        $level_mult = pow($element['factor'], $current_level);
        $points = ($resources_total * $level_mult) / Options::getInstance()->get('stat_points');

        return $points;
    }

    /**
     * Rebuild the user points for the current planet and specific structure type.
     *
     * @param int    $userId   The user ID
     * @param int    $planet_id The planet ID
     * @param string $what      The structure type (buildings|defenses|research|ships)
     *
     * @return boolean
     */
    public function rebuildPoints($userId, $planet_id, $what)
    {
        if (!in_array(config('DB_PREFIX') . $what, [BUILDINGS, DEFENSES, RESEARCH, SHIPS])) {
            return false;
        }

        $points = 0;
        $objects = Objects::getInstance()->getObjects();

        if ($what == 'research') {
            $objectsToUpdate = $this->statisticsLibraryModel->getResearchToUpdate($userId);
        } else {
            $objectsToUpdate = $this->statisticsLibraryModel->getPlanetElementToUpdate($what, $planet_id);
        }

        if (!is_null($objects)) {
            foreach ($objects as $id => $object) {
                if (isset($objectsToUpdate[$object])) {
                    $price = Objects::getInstance()->getPrice($id);
                    $total = $price['metal'] + $price['crystal'] + $price['deuterium'];
                    $level = $objectsToUpdate[$object];

                    if ($price['factor'] > 1) {
                        $s = (pow($price['factor'], $level) - 1) / ($price['factor'] - 1);
                    } else {
                        $s = $price['factor'] * $level;
                    }

                    $points += ($total * $s) / 1000;
                }
            }

            if ($points >= 0) {
                $what = strtr($what, ['research' => 'technology']);

                $this->statisticsLibraryModel->updatePoints($what, $points, $userId);

                return true;
            }
        }

        return false;
    }

    public function makeStats()
    {
        $this->time = time();
        $starttime = microtime(true);

        $result['initial_memory'] = [round(memory_get_usage() / 1024, 1), round(memory_get_usage(true) / 1024, 1)];

        self::makeUserRank();
        self::makeAllyRank();

        $endtime = microtime(true);

        $result['stats_time'] = $this->time;
        $result['totaltime'] = ($endtime - $starttime);
        $result['memory_peak'] = [round(memory_get_peak_usage() / 1024, 1), round(memory_get_peak_usage(true) / 1024, 1)];
        $result['end_memory'] = [round(memory_get_usage() / 1024, 1), round(memory_get_usage(true) / 1024, 1)];

        return $result;
    }

    private function makeUserRank(): void
    {
        // GET ALL DATA FROM THE USERS TO UPDATE
        $all_stats_data = $this->statisticsLibraryModel->getAllUserStatsData();

        // ANY USER ?
        if (empty($all_stats_data)) {
            return;
        }

        // BUILD ALL THE ARRAYS
        foreach ($all_stats_data as $CurUser) {
            $tech['old_rank'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_technology_rank'];
            $tech['points'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_technology_points'];

            $build['old_rank'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_buildings_rank'];
            $build['points'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_buildings_points'];

            $defs['old_rank'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_defenses_rank'];
            $defs['points'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_defenses_points'];

            $ships['old_rank'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_ships_rank'];
            $ships['points'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_ships_points'];

            $total['old_rank'][$CurUser['user_statistic_user_id']] = $CurUser['user_statistic_total_rank'];
            $total['points'][$CurUser['user_statistic_user_id']] = $CurUser['total_points'];
        }

        // ORDER THEM FROM HIGHEST TO LOWEST
        arsort($tech['points']);
        arsort($build['points']);
        arsort($defs['points']);
        arsort($ships['points']);
        arsort($total['points']);

        // ALL RANKS SHOULD START ON 1
        $rank['tech'] = 1;
        $rank['buil'] = 1;
        $rank['defe'] = 1;
        $rank['ship'] = 1;
        $rank['tota'] = 1;

        // TECH
        foreach ($tech as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $userId => $data) {
                    $tech['rank'][$userId] = $rank['tech']++;
                }
            }
        }

        // BUILDINGS
        foreach ($build as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $userId => $data) {
                    $build['rank'][$userId] = $rank['buil']++;
                }
            }
        }

        // DEFENSES
        foreach ($defs as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $userId => $data) {
                    $defs['rank'][$userId] = $rank['defe']++;
                }
            }
        }

        // SHIPS
        foreach ($ships as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $userId => $data) {
                    $ships['rank'][$userId] = $rank['ship']++;
                }
            }
        }

        // UPDATE QUERY
        $update_query = 'INSERT INTO ' . USERS_STATISTICS . '
                        (user_statistic_user_id,
                        user_statistic_buildings_old_rank,
                        user_statistic_buildings_rank,
                        user_statistic_defenses_old_rank,
                        user_statistic_defenses_rank,
                        user_statistic_ships_old_rank,
                        user_statistic_ships_rank,
                        user_statistic_technology_old_rank,
                        user_statistic_technology_rank,
                        user_statistic_total_points,
                        user_statistic_total_old_rank,
                        user_statistic_total_rank,
                        user_statistic_update_time) VALUES ';

        // SET VARIABLES
        $values = '';

        // TOTAL POINTS
        // UPDATE QUERY DYNAMIC BLOCK
        foreach ($total as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $userId => $data) {
                    $values .= '(' . $userId . ',
                                ' . $build['old_rank'][$userId] . ',
                                ' . $build['rank'][$userId] . ',
                                ' . $defs['old_rank'][$userId] . ',
                                ' . $defs['rank'][$userId] . ',
                                ' . $ships['old_rank'][$userId] . ',
                                ' . $ships['rank'][$userId] . ',
                                ' . $tech['old_rank'][$userId] . ',
                                ' . $tech['rank'][$userId] . ',
                                ' . $total['points'][$userId] . ',
                                ' . $total['old_rank'][$userId] . ',
                                ' . $rank['tota']++ . ',
                                ' . $this->time . '),';
                }
            }
        }

        // REMOVE LAST COMMA
        $values = substr_replace($values, '', -1);

        // FINISH UPDATE QUERY
        $update_query .= $values;
        $update_query .= ' ON DUPLICATE KEY UPDATE
								user_statistic_buildings_old_rank = VALUES(user_statistic_buildings_old_rank),
								user_statistic_buildings_rank = VALUES(user_statistic_buildings_rank),
								user_statistic_defenses_old_rank = VALUES(user_statistic_defenses_old_rank),
								user_statistic_defenses_rank = VALUES(user_statistic_defenses_rank),
								user_statistic_ships_old_rank = VALUES(user_statistic_ships_old_rank),
								user_statistic_ships_rank = VALUES(user_statistic_ships_rank),
								user_statistic_technology_old_rank = VALUES(user_statistic_technology_old_rank),
								user_statistic_technology_rank = VALUES(user_statistic_technology_rank),
								user_statistic_total_points = VALUES(user_statistic_total_points),
								user_statistic_total_old_rank = VALUES(user_statistic_total_old_rank),
								user_statistic_total_rank = VALUES(user_statistic_total_rank),
								user_statistic_update_time = VALUES(user_statistic_update_time);';

        // RUN QUERY
        $this->statisticsLibraryModel->runSingleQuery($update_query);

        // MEMORY CLEAN UP
        unset($all_stats_data, $build, $defs, $ships, $tech, $rank, $update_query, $values);
    }

    private function makeAllyRank(): void
    {
        // GET ALL DATA FROM THE USERS TO UPDATE
        $all_stats_data = $this->statisticsLibraryModel->getAllAllianceStatsData();

        // ANY ALLIANCE ?
        if (empty($all_stats_data)) {
            return;
        }

        // BUILD ALL THE ARRAYS
        foreach ($all_stats_data as $CurAlliance) {
            $tech['old_rank'][$CurAlliance['alliance_id']] = $CurAlliance['alliance_statistic_technology_rank'];
            $tech['points'][$CurAlliance['alliance_id']] = $CurAlliance['technology_points'];

            $build['old_rank'][$CurAlliance['alliance_id']] = $CurAlliance['alliance_statistic_buildings_rank'];
            $build['points'][$CurAlliance['alliance_id']] = $CurAlliance['buildings_points'];

            $defs['old_rank'][$CurAlliance['alliance_id']] = $CurAlliance['alliance_statistic_defenses_rank'];
            $defs['points'][$CurAlliance['alliance_id']] = $CurAlliance['defenses_points'];

            $ships['old_rank'][$CurAlliance['alliance_id']] = $CurAlliance['alliance_statistic_ships_rank'];
            $ships['points'][$CurAlliance['alliance_id']] = $CurAlliance['ships_points'];

            $total['old_rank'][$CurAlliance['alliance_id']] = $CurAlliance['alliance_statistic_total_rank'];
            $total['points'][$CurAlliance['alliance_id']] = $CurAlliance['total_points'];
        }

        // ORDER THEM FROM HIGHEST TO LOWEST
        arsort($tech['points']);
        arsort($build['points']);
        arsort($defs['points']);
        arsort($ships['points']);
        arsort($total['points']);

        // ALL RANKS SHOULD START ON 1
        $rank['tech'] = 1;
        $rank['buil'] = 1;
        $rank['defe'] = 1;
        $rank['ship'] = 1;
        $rank['tota'] = 1;

        // TECH
        foreach ($tech as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $alliance_id => $data) {
                    $tech['rank'][$alliance_id] = $rank['tech']++;
                }
            }
        }

        // BUILDINGS
        foreach ($build as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $alliance_id => $data) {
                    $build['rank'][$alliance_id] = $rank['buil']++;
                }
            }
        }

        // DEFENSES
        foreach ($defs as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $alliance_id => $data) {
                    $defs['rank'][$alliance_id] = $rank['defe']++;
                }
            }
        }

        // SHIPS
        foreach ($ships as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $alliance_id => $data) {
                    $ships['rank'][$alliance_id] = $rank['ship']++;
                }
            }
        }

        // UPDATE QUERY
        $update_query = 'INSERT INTO ' . ALLIANCE_STATISTICS . '
							(alliance_statistic_alliance_id,
								alliance_statistic_buildings_points,
								alliance_statistic_buildings_old_rank,
								alliance_statistic_buildings_rank,
								alliance_statistic_defenses_points,
								alliance_statistic_defenses_old_rank,
								alliance_statistic_defenses_rank,
								alliance_statistic_ships_points,
								alliance_statistic_ships_old_rank,
								alliance_statistic_ships_rank,
								alliance_statistic_technology_points,
								alliance_statistic_technology_old_rank,
								alliance_statistic_technology_rank,
								alliance_statistic_total_points,
								alliance_statistic_total_old_rank,
								alliance_statistic_total_rank,
								alliance_statistic_update_time) VALUES ';

        // SET VARIABLES
        $values = '';
        $update = '';

        // TOTAL POINTS
        // UPDATE QUERY DYNAMIC BLOCK
        foreach ($total as $key => $value) {
            if ($key == 'points') {
                foreach ($value as $alliance_id => $data) {
                    $values .= '(' . $alliance_id . ',
                                ' . $build['points'][$alliance_id] . ',
                                ' . $build['old_rank'][$alliance_id] . ',
                                ' . $build['rank'][$alliance_id] . ',
                                ' . $defs['points'][$alliance_id] . ',
                                ' . $defs['old_rank'][$alliance_id] . ',
                                ' . $defs['rank'][$alliance_id] . ',
                                ' . $ships['points'][$alliance_id] . ',
                                ' . $ships['old_rank'][$alliance_id] . ',
                                ' . $ships['rank'][$alliance_id] . ',
                                ' . $tech['points'][$alliance_id] . ',
                                ' . $tech['old_rank'][$alliance_id] . ',
                                ' . $tech['rank'][$alliance_id] . ',
                                ' . $total['points'][$alliance_id] . ',
                                ' . $total['old_rank'][$alliance_id] . ',
                                ' . $rank['tota']++ . ',
                                ' . $this->time . '),';
                }
            }
        }

        // REMOVE LAST COMMA
        $values = substr_replace($values, '', -1);

        // FINISH UPDATE QUERY
        $update_query .= $values;
        $update_query .= ' ON DUPLICATE KEY UPDATE
                            alliance_statistic_buildings_points = VALUES(alliance_statistic_buildings_points),
                            alliance_statistic_buildings_old_rank = VALUES(alliance_statistic_buildings_old_rank),
                            alliance_statistic_buildings_rank = VALUES(alliance_statistic_buildings_rank),
                            alliance_statistic_defenses_points = VALUES(alliance_statistic_defenses_points),
                            alliance_statistic_defenses_old_rank = VALUES(alliance_statistic_defenses_old_rank),
                            alliance_statistic_defenses_rank = VALUES(alliance_statistic_defenses_rank),
                            alliance_statistic_ships_points = VALUES(alliance_statistic_ships_points),
                            alliance_statistic_ships_old_rank = VALUES(alliance_statistic_ships_old_rank),
                            alliance_statistic_ships_rank = VALUES(alliance_statistic_ships_rank),
                            alliance_statistic_technology_points = VALUES(alliance_statistic_technology_points),
                            alliance_statistic_technology_old_rank = VALUES(alliance_statistic_technology_old_rank),
                            alliance_statistic_technology_rank = VALUES(alliance_statistic_technology_rank),
                            alliance_statistic_total_points = VALUES(alliance_statistic_total_points),
                            alliance_statistic_total_old_rank = VALUES(alliance_statistic_total_old_rank),
                            alliance_statistic_total_rank = VALUES(alliance_statistic_total_rank),
                            alliance_statistic_update_time = VALUES(alliance_statistic_update_time);';

        // RUN QUERY
        $this->statisticsLibraryModel->runSingleQuery($update_query);

        // MEMORY CLEAN UP
        unset($all_stats_data, $build, $defs, $ships, $tech, $rank, $update_query, $values);
    }
}
