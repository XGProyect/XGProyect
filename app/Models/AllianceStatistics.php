<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int   $alliance_statistic_alliance_id
 * @property int   $alliance_statistic_buildings_old_rank
 * @property int   $alliance_statistic_buildings_rank
 * @property int   $alliance_statistic_defenses_old_rank
 * @property int   $alliance_statistic_defenses_rank
 * @property int   $alliance_statistic_ships_old_rank
 * @property int   $alliance_statistic_ships_rank
 * @property int   $alliance_statistic_technology_old_rank
 * @property int   $alliance_statistic_technology_rank
 * @property int   $alliance_statistic_total_old_rank
 * @property int   $alliance_statistic_total_rank
 * @property int   $alliance_statistic_update_time
 * @property float $alliance_statistic_buildings_points
 * @property float $alliance_statistic_defenses_points
 * @property float $alliance_statistic_ships_points
 * @property float $alliance_statistic_technology_points
 * @property float $alliance_statistic_total_points
 */
class AllianceStatistics extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alliance_statistics';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'alliance_statistic_alliance_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'alliance_statistic_buildings_points',
        'alliance_statistic_buildings_old_rank',
        'alliance_statistic_buildings_rank',
        'alliance_statistic_defenses_points',
        'alliance_statistic_defenses_old_rank',
        'alliance_statistic_defenses_rank',
        'alliance_statistic_ships_points',
        'alliance_statistic_ships_old_rank',
        'alliance_statistic_ships_rank',
        'alliance_statistic_technology_points',
        'alliance_statistic_technology_old_rank',
        'alliance_statistic_technology_rank',
        'alliance_statistic_total_points',
        'alliance_statistic_total_old_rank',
        'alliance_statistic_total_rank',
        'alliance_statistic_update_time',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'alliance_statistic_alliance_id' => 'int',
        'alliance_statistic_buildings_points' => 'double',
        'alliance_statistic_buildings_old_rank' => 'int',
        'alliance_statistic_buildings_rank' => 'int',
        'alliance_statistic_defenses_points' => 'double',
        'alliance_statistic_defenses_old_rank' => 'int',
        'alliance_statistic_defenses_rank' => 'int',
        'alliance_statistic_ships_points' => 'double',
        'alliance_statistic_ships_old_rank' => 'int',
        'alliance_statistic_ships_rank' => 'int',
        'alliance_statistic_technology_points' => 'double',
        'alliance_statistic_technology_old_rank' => 'int',
        'alliance_statistic_technology_rank' => 'int',
        'alliance_statistic_total_points' => 'double',
        'alliance_statistic_total_old_rank' => 'int',
        'alliance_statistic_total_rank' => 'int',
        'alliance_statistic_update_time' => 'int',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var list<string>
     */
    protected $dates = [

    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
