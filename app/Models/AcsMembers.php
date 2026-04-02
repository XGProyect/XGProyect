<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $acs_member_id
 * @property int $acs_group_id
 * @property int $acs_user_id
 */
class AcsMembers extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'acs_members';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'acs_member_id';

    /**
     * Attributes that should be mass-assignable.
     *
    * @var list<string>
     */
    protected $fillable = [
        'acs_group_id',
        'acs_user_id',
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
        'acs_member_id' => 'int',
        'acs_group_id' => 'int',
        'acs_user_id' => 'int',
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
