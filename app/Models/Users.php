<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $user_name
 * @property string $user_password
 * @property string $user_email
 * @property string $user_lastip
 * @property string $user_ip_at_reg
 * @property string $user_agent
 * @property string $user_current_page
 * @property string $user_fleet_shortcuts
 * @property string $user_ally_request_text
 * @property int    $user_authlevel
 * @property int    $user_home_planet_id
 * @property int    $user_galaxy
 * @property int    $user_system
 * @property int    $user_planet
 * @property int    $user_current_planet
 * @property int    $user_register_time
 * @property int    $user_onlinetime
 * @property int    $user_ally_id
 * @property int    $user_ally_request
 * @property int    $user_ally_register_time
 * @property int    $user_ally_rank_id
 * @property int    $user_banned
 */
class Users extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'user_password', 'user_email', 'user_authlevel', 'user_home_planet_id', 'user_galaxy', 'user_system', 'user_planet', 'user_current_planet', 'user_lastip', 'user_ip_at_reg', 'user_agent', 'user_current_page', 'user_register_time', 'user_onlinetime', 'user_fleet_shortcuts', 'user_ally_id', 'user_ally_request', 'user_ally_request_text', 'user_ally_register_time', 'user_ally_rank_id', 'user_banned'
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
        'user_name' => 'string', 'user_password' => 'string', 'user_email' => 'string', 'user_authlevel' => 'int', 'user_home_planet_id' => 'int', 'user_galaxy' => 'int', 'user_system' => 'int', 'user_planet' => 'int', 'user_current_planet' => 'int', 'user_lastip' => 'string', 'user_ip_at_reg' => 'string', 'user_agent' => 'string', 'user_current_page' => 'string', 'user_register_time' => 'int', 'user_onlinetime' => 'int', 'user_fleet_shortcuts' => 'string', 'user_ally_id' => 'int', 'user_ally_request' => 'int', 'user_ally_request_text' => 'string', 'user_ally_register_time' => 'int', 'user_ally_rank_id' => 'int', 'user_banned' => 'int'
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
    public function planets(): HasMany
    {
        return $this->hasMany(Planets::class, 'planet_user_id');
    }
}
