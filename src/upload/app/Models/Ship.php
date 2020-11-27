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

class Ship extends Model
{
    protected $table = 'ships';
    protected $primaryKey = 'ship_id';
    public $timestamps = false;
    protected $fillable = [
        'ship_planet_id',
        'ship_small_cargo_ship',
        'ship_big_cargo_ship',
        'ship_light_fighter',
        'ship_heavy_fighter',
        'ship_cruiser',
        'ship_battleship',
        'ship_colony_ship',
        'ship_recycler',
        'ship_espionage_probe',
        'ship_bomber',
        'ship_solar_satellite',
        'ship_destroyer',
        'ship_deathstar',
        'ship_battlecruiser',
    ];
    protected $returnType = 'App\Entities\Ship';
}
