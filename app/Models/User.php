<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int                $id
 * @property string             $name
 * @property string             $password
 * @property string             $email
 * @property string             $lastip
 * @property string             $ip_at_reg
 * @property string             $agent
 * @property string             $current_page
 * @property string             $fleet_shortcuts
 * @property int                $authlevel
 * @property int                $home_planet_id
 * @property int                $galaxy
 * @property int                $system
 * @property int                $planet
 * @property int                $current_planet
 * @property int                $register_time
 * @property int                $onlinetime
 * @property int                $ally_id
 * @property int                $ally_request
 * @property string             $ally_request_text
 * @property int                $ally_register_time
 * @property int                $ally_rank_id
 * @property Ban|null           $ban
 * @property Planets            $planets
 * @property Preferences        $preferences
 * @property Premium            $premium
 * @property Research                         $research
 * @property Collection<int,ResearchQueue>    $researchQueue
 * @property UsersStatistics                  $stats
 */
class User extends Authenticatable
{
    use HasApiTokens;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
    * @var list<string>
     */
    protected $fillable = [
        'name',
        'password',
        'email',
        'authlevel',
        'home_planet_id',
        'galaxy',
        'system',
        'planet',
        'current_planet',
        'lastip',
        'ip_at_reg',
        'agent',
        'current_page',
        'register_time',
        'onlinetime',
        'fleet_shortcuts',
        'ally_id',
        'ally_request',
        'ally_request_text',
        'ally_register_time',
        'ally_rank_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
    * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'int',
        'name' => 'string',
        'password' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'authlevel' => 'int',
        'home_planet_id' => 'int',
        'galaxy' => 'int',
        'system' => 'int',
        'planet' => 'int',
        'current_planet' => 'int',
        'lastip' => 'string',
        'ip_at_reg' => 'string',
        'agent' => 'string',
        'current_page' => 'string',
        'register_time' => 'int',
        'onlinetime' => 'int',
        'fleet_shortcuts' => 'string',
        'ally_id' => 'int',
        'ally_request' => 'int',
        'ally_request_text' => 'string',
        'ally_register_time' => 'int',
        'ally_rank_id' => 'int',
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
    public function setPasswordAttribute(string $input): void
    {
        $this->attributes['password'] = Hash::make($input);
    }

    // Relations ...
    /** @return HasOne<Ban, $this> */
    public function ban(): HasOne
    {
        return $this->hasOne(Ban::class);
    }

    /** @return HasMany<Planets, $this> */
    public function planets(): HasMany
    {
        return $this->hasMany(Planets::class, 'planet_user_id');
    }

    /** @return HasOne<Preferences, $this> */
    public function preferences(): HasOne
    {
        return $this->hasOne(Preferences::class, 'preference_user_id');
    }

    /** @return HasOne<Premium, $this> */
    public function premium(): HasOne
    {
        return $this->hasOne(Premium::class, 'premium_user_id');
    }

    /** @return HasOne<Research, $this> */
    public function research(): HasOne
    {
        return $this->hasOne(Research::class, 'research_user_id');
    }

    /** @return HasMany<ResearchQueue, $this> */
    public function researchQueue(): HasMany
    {
        return $this->hasMany(ResearchQueue::class, 'user_id');
    }

    /** @return HasOne<UsersStatistics, $this> */
    public function stats(): HasOne
    {
        return $this->hasOne(UsersStatistics::class, 'user_statistic_user_id');
    }
}
