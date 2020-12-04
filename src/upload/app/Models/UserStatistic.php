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
 * @since      4.0.0
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatistic extends Model
{
    protected $table = 'users_statistics';
    protected $primaryKey = 'user_statistic_id';
    public $timestamps = false;
    protected $fillable = [
        'user_statistic_user_id',
        'user_statistic_buildings_points',
        'user_statistic_buildings_old_rank',
        'user_statistic_buildings_rank',
        'user_statistic_defenses_points',
        'user_statistic_defenses_old_rank',
        'user_statistic_defenses_rank',
        'user_statistic_ships_points',
        'user_statistic_ships_old_rank',
        'user_statistic_ships_rank',
        'user_statistic_technology_points',
        'user_statistic_technology_old_rank',
        'user_statistic_technology_rank',
        'user_statistic_total_points',
        'user_statistic_total_old_rank',
        'user_statistic_total_rank',
        'user_statistic_update_time',
    ];
}
