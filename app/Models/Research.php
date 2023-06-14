<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $research_id
 * @property int $research_user_id
 * @property int $research_current_research
 * @property int $research_espionage_technology
 * @property int $research_computer_technology
 * @property int $research_weapons_technology
 * @property int $research_shielding_technology
 * @property int $research_armour_technology
 * @property int $research_energy_technology
 * @property int $research_hyperspace_technology
 * @property int $research_combustion_drive
 * @property int $research_impulse_drive
 * @property int $research_hyperspace_drive
 * @property int $research_laser_technology
 * @property int $research_ionic_technology
 * @property int $research_plasma_technology
 * @property int $research_intergalactic_research_network
 * @property int $research_astrophysics
 * @property int $research_graviton_technology
 */
class Research extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'research';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'research_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'research_user_id',
        'research_current_research',
        'research_espionage_technology',
        'research_computer_technology',
        'research_weapons_technology',
        'research_shielding_technology',
        'research_armour_technology',
        'research_energy_technology',
        'research_hyperspace_technology',
        'research_combustion_drive',
        'research_impulse_drive',
        'research_hyperspace_drive',
        'research_laser_technology',
        'research_ionic_technology',
        'research_plasma_technology',
        'research_intergalactic_research_network',
        'research_astrophysics',
        'research_graviton_technology',
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
        'research_id' => 'int',
        'research_user_id' => 'int',
        'research_current_research' => 'int',
        'research_espionage_technology' => 'int',
        'research_computer_technology' => 'int',
        'research_weapons_technology' => 'int',
        'research_shielding_technology' => 'int',
        'research_armour_technology' => 'int',
        'research_energy_technology' => 'int',
        'research_hyperspace_technology' => 'int',
        'research_combustion_drive' => 'int',
        'research_impulse_drive' => 'int',
        'research_hyperspace_drive' => 'int',
        'research_laser_technology' => 'int',
        'research_ionic_technology' => 'int',
        'research_plasma_technology' => 'int',
        'research_intergalactic_research_network' => 'int',
        'research_astrophysics' => 'int',
        'research_graviton_technology' => 'int',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
