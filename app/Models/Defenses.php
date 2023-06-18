<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $defense_id
 * @property int $defense_planet_id
 * @property int $defense_rocket_launcher
 * @property int $defense_light_laser
 * @property int $defense_heavy_laser
 * @property int $defense_ion_cannon
 * @property int $defense_gauss_cannon
 * @property int $defense_plasma_turret
 * @property int $defense_small_shield_dome
 * @property int $defense_large_shield_dome
 * @property int $defense_anti-ballistic_missile
 * @property int $defense_interplanetary_missile
 */
class Defenses extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'defenses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'defense_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'defense_planet_id',
        'defense_rocket_launcher',
        'defense_light_laser',
        'defense_heavy_laser',
        'defense_ion_cannon',
        'defense_gauss_cannon',
        'defense_plasma_turret',
        'defense_small_shield_dome',
        'defense_large_shield_dome',
        'defense_anti-ballistic_missile',
        'defense_interplanetary_missile',
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
        'defense_id' => 'int',
        'defense_planet_id' => 'int',
        'defense_rocket_launcher' => 'int',
        'defense_light_laser' => 'int',
        'defense_heavy_laser' => 'int',
        'defense_ion_cannon' => 'int',
        'defense_gauss_cannon' => 'int',
        'defense_plasma_turret' => 'int',
        'defense_small_shield_dome' => 'int',
        'defense_large_shield_dome' => 'int',
        'defense_anti-ballistic_missile' => 'int',
        'defense_interplanetary_missile' => 'int',
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
