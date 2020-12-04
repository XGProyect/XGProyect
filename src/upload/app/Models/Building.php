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

class Building extends Model
{
    protected $table = 'buildings';
    protected $primaryKey = 'building_id';
    public $timestamps = false;
    protected $fillable = [
        'building_planet_id',
        'building_metal_mine',
        'building_crystal_mine',
        'building_deuterium_sintetizer',
        'building_solar_plant',
        'building_fusion_reactor',
        'building_robot_factory',
        'building_nano_factory',
        'building_hangar',
        'building_metal_store',
        'building_crystal_store',
        'building_deuterium_tank',
        'building_laboratory',
        'building_terraformer',
        'building_ally_deposit',
        'building_missile_silo',
        'building_mondbasis',
        'building_phalanx',
        'building_jump_gate',
    ];
}
