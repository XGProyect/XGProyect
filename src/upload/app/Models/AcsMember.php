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

class AcsMember extends Model
{
    protected $table = 'acs_members';
    protected $primaryKey = 'acs_member_id';
    protected $allowedFields = [
        'acs_group_id',
        'acs_user_id',
    ];
    protected $returnType = 'App\Entities\AcsMember';
}
