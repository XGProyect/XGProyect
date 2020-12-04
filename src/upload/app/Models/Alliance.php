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

class Alliance extends Model
{
    protected $table = 'alliance';
    protected $primaryKey = 'alliance_id';
    public $timestamps = false;
    protected $fillable = [
        'alliance_name',
        'alliance_tag',
        'alliance_owner',
        'alliance_register_time',
        'alliance_description',
        'alliance_web',
        'alliance_text',
        'alliance_image',
        'alliance_request',
        'alliance_request_notallow',
        'alliance_owner_range',
        'alliance_ranks',
    ];
}
