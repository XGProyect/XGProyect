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

class Fleet extends Model
{
    protected $table = 'fleets';
    protected $primaryKey = 'fleet_id';
    public $timestamps = false;
    protected $fillable = [
        'fleet_owner',
        'fleet_mission',
        'fleet_amount',
        'fleet_array',
        'fleet_start_time',
        'fleet_start_galaxy',
        'fleet_start_system',
        'fleet_start_planet',
        'fleet_start_type',
        'fleet_end_time',
        'fleet_end_stay',
        'fleet_end_galaxy',
        'fleet_end_system',
        'fleet_end_planet',
        'fleet_end_type',
        'fleet_target_obj',
        'fleet_resource_metal',
        'fleet_resource_crystal',
        'fleet_resource_deuterium',
        'fleet_fuel',
        'fleet_target_owner',
        'fleet_group',
        'fleet_mess',
    ];
}
