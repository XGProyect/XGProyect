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

class Premium extends Model
{
    protected $table = 'premium';
    protected $primaryKey = 'premium_id';
    public $timestamps = false;
    protected $fillable = [
        'premium_user_id',
        'premium_dark_matter',
        'premium_officier_commander',
        'premium_officier_admiral',
        'premium_officier_engineer',
        'premium_officier_geologist',
        'premium_officier_technocrat',
    ];
}
