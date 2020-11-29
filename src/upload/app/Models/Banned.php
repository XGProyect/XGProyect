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

class Banned extends Model
{
    protected $table = 'banned';
    protected $primaryKey = 'banned_id';
    public $timestamps = false;
    protected $fillable = [
        'banned_who',
        'banned_theme',
        'banned_time',
        'banned_longer',
        'banned_author',
        'banned_email',
    ];
}
