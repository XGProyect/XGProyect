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

class Defense extends Model
{
    protected $table = 'defenses';
    protected $primaryKey = 'defense_id';
    public $timestamps = false;
    protected $allowedFields = [
        'defense_planet_id',
        'defense_rocket_launcher',
        'defense_light_laser',
        'defense_heavy_laser',
        'defense_ion_cannon',
        'defense_gauss_cannon',
        'defense_plasma_turret',
        'defense_small_shield_dome',
        'defense_large_shield_dome',
        'defense_anti-ballistic_missile',
        'defense_interplanetary_missile',
    ];
    protected $returnType = 'App\Entities\Defense';
}
