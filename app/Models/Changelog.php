<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $changelog_id
 * @property int    $changelog_lang_id
 * @property string $changelog_version
 * @property string $changelog_description
 * @property Date   $changelog_date
 */
class Changelog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'changelog';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'changelog_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'changelog_lang_id', 'changelog_version', 'changelog_date', 'changelog_description'
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
        'changelog_id' => 'int', 'changelog_lang_id' => 'int', 'changelog_version' => 'string', 'changelog_date' => 'date', 'changelog_description' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'changelog_date'
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
