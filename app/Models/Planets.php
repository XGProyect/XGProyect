<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $planet_name
 * @property string $planet_b_building_id
 * @property string $planet_b_hangar_id
 * @property string $planet_image
 * @property int    $planet_user_id
 * @property int    $planet_galaxy
 * @property int    $planet_system
 * @property int    $planet_planet
 * @property int    $planet_last_update
 * @property int    $planet_type
 * @property int    $planet_destroyed
 * @property int    $planet_b_building
 * @property int    $planet_b_tech
 * @property int    $planet_b_tech_id
 * @property int    $planet_b_hangar
 * @property int    $planet_diameter
 * @property int    $planet_field_current
 * @property int    $planet_field_max
 * @property int    $planet_temp_min
 * @property int    $planet_temp_max
 * @property int    $planet_metal_perhour
 * @property int    $planet_crystal_perhour
 * @property int    $planet_deuterium_perhour
 * @property int    $planet_energy_used
 * @property int    $planet_energy_max
 * @property int    $planet_building_metal_mine_percent
 * @property int    $planet_building_crystal_mine_percent
 * @property int    $planet_building_deuterium_sintetizer_percent
 * @property int    $planet_building_solar_plant_percent
 * @property int    $planet_building_fusion_reactor_percent
 * @property int    $planet_ship_solar_satellite_percent
 * @property int    $planet_last_jump_time
 * @property int    $planet_invisible_start_time
 * @property float  $planet_metal
 * @property float  $planet_crystal
 * @property float  $planet_deuterium
 */
class Planets extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'planets';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'planet_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'planet_name',
        'planet_user_id',
        'planet_galaxy',
        'planet_system',
        'planet_planet',
        'planet_last_update',
        'planet_type',
        'planet_destroyed',
        'planet_b_building',
        'planet_b_building_id',
        'planet_b_tech',
        'planet_b_tech_id',
        'planet_b_hangar',
        'planet_b_hangar_id',
        'planet_image',
        'planet_diameter',
        'planet_field_current',
        'planet_field_max',
        'planet_temp_min',
        'planet_temp_max',
        'planet_metal',
        'planet_metal_perhour',
        'planet_crystal',
        'planet_crystal_perhour',
        'planet_deuterium',
        'planet_deuterium_perhour',
        'planet_energy_used',
        'planet_energy_max',
        'planet_building_metal_mine_percent',
        'planet_building_crystal_mine_percent',
        'planet_building_deuterium_sintetizer_percent',
        'planet_building_solar_plant_percent',
        'planet_building_fusion_reactor_percent',
        'planet_ship_solar_satellite_percent',
        'planet_last_jump_time',
        'planet_debris_metal',
        'planet_debris_crystal',
        'planet_invisible_start_time',
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
        'planet_name' => 'string',
        'planet_user_id' => 'int',
        'planet_galaxy' => 'int',
        'planet_system' => 'int',
        'planet_planet' => 'int',
        'planet_last_update' => 'int',
        'planet_type' => 'int',
        'planet_destroyed' => 'int',
        'planet_b_building' => 'int',
        'planet_b_building_id' => 'string',
        'planet_b_tech' => 'int',
        'planet_b_tech_id' => 'int',
        'planet_b_hangar' => 'int',
        'planet_b_hangar_id' => 'string',
        'planet_image' => 'string',
        'planet_diameter' => 'int',
        'planet_field_current' => 'int',
        'planet_field_max' => 'int',
        'planet_temp_min' => 'int',
        'planet_temp_max' => 'int',
        'planet_metal' => 'double',
        'planet_metal_perhour' => 'int',
        'planet_crystal' => 'double',
        'planet_crystal_perhour' => 'int',
        'planet_deuterium' => 'double',
        'planet_deuterium_perhour' => 'int',
        'planet_energy_used' => 'int',
        'planet_energy_max' => 'int',
        'planet_building_metal_mine_percent' => 'int',
        'planet_building_crystal_mine_percent' => 'int',
        'planet_building_deuterium_sintetizer_percent' => 'int',
        'planet_building_solar_plant_percent' => 'int',
        'planet_building_fusion_reactor_percent' => 'int',
        'planet_ship_solar_satellite_percent' => 'int',
        'planet_last_jump_time' => 'int',
        'planet_invisible_start_time' => 'int',
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
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'planet_user_id');
    }

    public function buildings(): HasOne
    {
        return $this->hasOne(Buildings::class, 'building_planet_id');
    }

    public function defenses(): HasOne
    {
        return $this->hasOne(Defenses::class, 'defense_planet_id');
    }

    public function ships(): HasOne
    {
        return $this->hasOne(Ships::class, 'ship_planet_id');
    }
}
