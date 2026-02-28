<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int           $changelog_id
 * @property int           $changelog_lang_id
 * @property string        $changelog_version
 * @property string        $changelog_description
 * @property \Carbon\Carbon $changelog_date
 * @property-read Languages $language
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
        'changelog_lang_id',
        'changelog_version',
        'changelog_date',
        'changelog_description',
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
        'changelog_id' => 'int',
        'changelog_lang_id' => 'int',
        'changelog_version' => 'string',
        'changelog_date' => 'date',
        'changelog_description' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'changelog_date',
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
    public function language(): HasOne
    {
        return $this->hasOne(Languages::class, 'id', 'changelog_lang_id');
    }
}
