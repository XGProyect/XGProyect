<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $preference_id
 * @property int     $preference_user_id
 * @property int     $preference_nickname_change
 * @property int     $preference_spy_probes
 * @property int     $preference_vacation_mode
 * @property int     $preference_delete_mode
 * @property boolean $preference_planet_sort
 * @property boolean $preference_planet_sort_sequence
 */
class Preferences extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'preferences';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'preference_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'preference_user_id',
        'preference_nickname_change',
        'preference_spy_probes',
        'preference_planet_sort',
        'preference_planet_sort_sequence',
        'preference_vacation_mode',
        'preference_delete_mode',
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
        'preference_id' => 'int',
        'preference_user_id' => 'int',
        'preference_nickname_change' => 'int',
        'preference_spy_probes' => 'int',
        'preference_planet_sort' => 'boolean',
        'preference_planet_sort_sequence' => 'boolean',
        'preference_vacation_mode' => 'int',
        'preference_delete_mode' => 'int',
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
