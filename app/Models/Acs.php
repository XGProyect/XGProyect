<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string  $acs_name
 * @property int     $acs_owner
 * @property int     $acs_galaxy
 * @property int     $acs_system
 * @property int     $acs_planet
 * @property boolean $acs_planet_type
 */
class Acs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'acs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'acs_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'acs_name',
        'acs_owner',
        'acs_galaxy',
        'acs_system',
        'acs_planet',
        'acs_planet_type',
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
        'acs_name' => 'string',
        'acs_owner' => 'int',
        'acs_galaxy' => 'int',
        'acs_system' => 'int',
        'acs_planet' => 'int',
        'acs_planet_type' => 'boolean',
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
}
