<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int   $user_statistic_user_id
 * @property int   $user_statistic_buildings_old_rank
 * @property int   $user_statistic_buildings_rank
 * @property int   $user_statistic_defenses_old_rank
 * @property int   $user_statistic_defenses_rank
 * @property int   $user_statistic_ships_old_rank
 * @property int   $user_statistic_ships_rank
 * @property int   $user_statistic_technology_old_rank
 * @property int   $user_statistic_technology_rank
 * @property int   $user_statistic_total_old_rank
 * @property int   $user_statistic_total_rank
 * @property int   $user_statistic_update_time
 * @property float $user_statistic_buildings_points
 * @property float $user_statistic_defenses_points
 * @property float $user_statistic_ships_points
 * @property float $user_statistic_technology_points
 * @property float $user_statistic_total_points
 */
class UsersStatistics extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_statistics';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_statistic_user_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_statistic_buildings_points', 'user_statistic_buildings_old_rank', 'user_statistic_buildings_rank', 'user_statistic_defenses_points', 'user_statistic_defenses_old_rank', 'user_statistic_defenses_rank', 'user_statistic_ships_points', 'user_statistic_ships_old_rank', 'user_statistic_ships_rank', 'user_statistic_technology_points', 'user_statistic_technology_old_rank', 'user_statistic_technology_rank', 'user_statistic_total_points', 'user_statistic_total_old_rank', 'user_statistic_total_rank', 'user_statistic_update_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_statistic_user_id' => 'int', 'user_statistic_buildings_points' => 'double', 'user_statistic_buildings_old_rank' => 'int', 'user_statistic_buildings_rank' => 'int', 'user_statistic_defenses_points' => 'double', 'user_statistic_defenses_old_rank' => 'int', 'user_statistic_defenses_rank' => 'int', 'user_statistic_ships_points' => 'double', 'user_statistic_ships_old_rank' => 'int', 'user_statistic_ships_rank' => 'int', 'user_statistic_technology_points' => 'double', 'user_statistic_technology_old_rank' => 'int', 'user_statistic_technology_rank' => 'int', 'user_statistic_total_points' => 'double', 'user_statistic_total_old_rank' => 'int', 'user_statistic_total_rank' => 'int', 'user_statistic_update_time' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    // Relations ...
}
