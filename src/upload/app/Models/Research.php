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

class Research extends Model
{
    protected $table = 'research';
    protected $primaryKey = 'research_id';
    public $timestamps = false;
    protected $allowedFields = [
        'research_user_id',
        'research_current_research',
        'research_espionage_technology',
        'research_computer_technology',
        'research_weapons_technology',
        'research_shielding_technology',
        'research_armour_technology',
        'research_energy_technology',
        'research_hyperspace_technology',
        'research_combustion_drive',
        'research_impulse_drive',
        'research_hyperspace_drive',
        'research_laser_technology',
        'research_ionic_technology',
        'research_plasma_technology',
        'research_intergalactic_research_network',
        'research_astrophysics',
        'research_graviton_technology',
    ];
    protected $returnType = 'App\Entities\Research';
}
