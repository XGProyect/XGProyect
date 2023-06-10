<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $option_id
 * @property string $option_name
 * @property string $option_value
 */
class Options extends Model
{
    public const UPDATED_AT = null;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'options';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'option_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'option_name', 'option_value'
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
        'option_name' => 'string', 'option_value' => 'string'
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
}
