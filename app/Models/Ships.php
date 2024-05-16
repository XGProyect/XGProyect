<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $ship_id
 * @property int $ship_planet_id
 * @property int $ship_small_cargo_ship
 * @property int $ship_big_cargo_ship
 * @property int $ship_light_fighter
 * @property int $ship_heavy_fighter
 * @property int $ship_cruiser
 * @property int $ship_battleship
 * @property int $ship_colony_ship
 * @property int $ship_recycler
 * @property int $ship_espionage_probe
 * @property int $ship_bomber
 * @property int $ship_solar_satellite
 * @property int $ship_destroyer
 * @property int $ship_deathstar
 * @property int $ship_battlecruiser
 */
class Ships extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ships';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ship_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ship_planet_id',
        'ship_small_cargo_ship',
        'ship_big_cargo_ship',
        'ship_light_fighter',
        'ship_heavy_fighter',
        'ship_cruiser',
        'ship_battleship',
        'ship_colony_ship',
        'ship_recycler',
        'ship_espionage_probe',
        'ship_bomber',
        'ship_solar_satellite',
        'ship_destroyer',
        'ship_deathstar',
        'ship_battlecruiser',
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
        'ship_id' => 'int',
        'ship_planet_id' => 'int',
        'ship_small_cargo_ship' => 'int',
        'ship_big_cargo_ship' => 'int',
        'ship_light_fighter' => 'int',
        'ship_heavy_fighter' => 'int',
        'ship_cruiser' => 'int',
        'ship_battleship' => 'int',
        'ship_colony_ship' => 'int',
        'ship_recycler' => 'int',
        'ship_espionage_probe' => 'int',
        'ship_bomber' => 'int',
        'ship_solar_satellite' => 'int',
        'ship_destroyer' => 'int',
        'ship_deathstar' => 'int',
        'ship_battlecruiser' => 'int',
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
