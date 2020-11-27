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

class Preference extends Model
{
    protected $table = 'preferences';
    protected $primaryKey = 'preference_id';
    public $timestamps = false;
    protected $allowedFields = [
        'preference_user_id',
        'preference_nickname_change',
        'preference_spy_probes',
        'preference_planet_sort',
        'preference_planet_sort_sequence',
        'preference_vacation_mode',
        'preference_delete_mode',
    ];
    protected $returnType = 'App\Entities\Preference';
}
