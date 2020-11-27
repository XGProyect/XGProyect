<?php
/**
 * XG Proyect
 *
 * Open-source OGame Clon
 *
 * This content is released under the GPL-3.0 License
 *
 * Copyright (c) 2008-2021 XG Proyect
 *
 * @package    XG Proyect
 * @author     XG Proyect Team
 * @copyright  2008-2021 XG Proyect
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0 License
 * @link       https://github.com/XGProyect/
 * @since      Version 4.0.0
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceStatistic extends Model
{
    protected $table = 'alliance_statistics';
    protected $primaryKey = 'alliance_statistic_alliance_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $allowedFields = [
        'alliance_statistic_buildings_points',
        'alliance_statistic_buildings_old_rank',
        'alliance_statistic_buildings_rank',
        'alliance_statistic_defenses_points',
        'alliance_statistic_defenses_old_rank',
        'alliance_statistic_defenses_rank',
        'alliance_statistic_ships_points',
        'alliance_statistic_ships_old_rank',
        'alliance_statistic_ships_rank',
        'alliance_statistic_technology_points',
        'alliance_statistic_technology_old_rank',
        'alliance_statistic_technology_rank',
        'alliance_statistic_total_points',
        'alliance_statistic_total_old_rank',
        'alliance_statistic_total_rank',
        'alliance_statistic_update_time',
    ];
    protected $returnType = 'App\Entities\AllianceStatistic';
}
