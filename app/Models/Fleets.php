<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $fleet_owner
 * @property int     $fleet_mission
 * @property int     $fleet_start_time
 * @property int     $fleet_start_galaxy
 * @property int     $fleet_start_system
 * @property int     $fleet_start_planet
 * @property int     $fleet_start_type
 * @property int     $fleet_end_time
 * @property int     $fleet_end_stay
 * @property int     $fleet_end_galaxy
 * @property int     $fleet_end_system
 * @property int     $fleet_end_planet
 * @property int     $fleet_end_type
 * @property int     $fleet_target_obj
 * @property int     $fleet_target_owner
 * @property int     $fleet_creation
 * @property string  $fleet_array
 * @property string  $fleet_group
 * @property boolean $fleet_mess
 */
class Fleets extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'fleets';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'fleet_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fleet_owner',
        'fleet_mission',
        'fleet_amount',
        'fleet_array',
        'fleet_start_time',
        'fleet_start_galaxy',
        'fleet_start_system',
        'fleet_start_planet',
        'fleet_start_type',
        'fleet_end_time',
        'fleet_end_stay',
        'fleet_end_galaxy',
        'fleet_end_system',
        'fleet_end_planet',
        'fleet_end_type',
        'fleet_target_obj',
        'fleet_resource_metal',
        'fleet_resource_crystal',
        'fleet_resource_deuterium',
        'fleet_fuel',
        'fleet_target_owner',
        'fleet_group',
        'fleet_mess',
        'fleet_creation',
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
        'fleet_owner' => 'int',
        'fleet_mission' => 'int',
        'fleet_array' => 'string',
        'fleet_start_time' => 'int',
        'fleet_start_galaxy' => 'int',
        'fleet_start_system' => 'int',
        'fleet_start_planet' => 'int',
        'fleet_start_type' => 'int',
        'fleet_end_time' => 'int',
        'fleet_end_stay' => 'int',
        'fleet_end_galaxy' => 'int',
        'fleet_end_system' => 'int',
        'fleet_end_planet' => 'int',
        'fleet_end_type' => 'int',
        'fleet_target_obj' => 'int',
        'fleet_target_owner' => 'int',
        'fleet_group' => 'string',
        'fleet_mess' => 'boolean',
        'fleet_creation' => 'int',
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
