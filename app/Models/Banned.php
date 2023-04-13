<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $banned_who
 * @property string $banned_theme
 * @property string $banned_author
 * @property string $banned_email
 * @property int    $banned_time
 * @property int    $banned_longer
 */
class Banned extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banned';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'banned_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'banned_who', 'banned_theme', 'banned_time', 'banned_longer', 'banned_author', 'banned_email'
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
        'banned_who' => 'string', 'banned_theme' => 'string', 'banned_time' => 'int', 'banned_longer' => 'int', 'banned_author' => 'string', 'banned_email' => 'string'
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
