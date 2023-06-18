<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $building_id
 * @property int $building_planet_id
 * @property int $building_metal_mine
 * @property int $building_crystal_mine
 * @property int $building_deuterium_sintetizer
 * @property int $building_solar_plant
 * @property int $building_fusion_reactor
 * @property int $building_robot_factory
 * @property int $building_nano_factory
 * @property int $building_hangar
 * @property int $building_metal_store
 * @property int $building_crystal_store
 * @property int $building_deuterium_tank
 * @property int $building_laboratory
 * @property int $building_terraformer
 * @property int $building_ally_deposit
 * @property int $building_missile_silo
 * @property int $building_mondbasis
 * @property int $building_phalanx
 * @property int $building_jump_gate
 */
class Buildings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buildings';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'building_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_planet_id',
        'building_metal_mine',
        'building_crystal_mine',
        'building_deuterium_sintetizer',
        'building_solar_plant',
        'building_fusion_reactor',
        'building_robot_factory',
        'building_nano_factory',
        'building_hangar',
        'building_metal_store',
        'building_crystal_store',
        'building_deuterium_tank',
        'building_laboratory',
        'building_terraformer',
        'building_ally_deposit',
        'building_missile_silo',
        'building_mondbasis',
        'building_phalanx',
        'building_jump_gate',
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
        'building_id' => 'int',
        'building_planet_id' => 'int',
        'building_metal_mine' => 'int',
        'building_crystal_mine' => 'int',
        'building_deuterium_sintetizer' => 'int',
        'building_solar_plant' => 'int',
        'building_fusion_reactor' => 'int',
        'building_robot_factory' => 'int',
        'building_nano_factory' => 'int',
        'building_hangar' => 'int',
        'building_metal_store' => 'int',
        'building_crystal_store' => 'int',
        'building_deuterium_tank' => 'int',
        'building_laboratory' => 'int',
        'building_terraformer' => 'int',
        'building_ally_deposit' => 'int',
        'building_missile_silo' => 'int',
        'building_mondbasis' => 'int',
        'building_phalanx' => 'int',
        'building_jump_gate' => 'int',
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
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planets::class);
    }
}
