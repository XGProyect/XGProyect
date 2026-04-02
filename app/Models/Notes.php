<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $note_owner
 * @property int     $note_time
 * @property boolean $note_priority
 * @property string  $note_title
 * @property string  $note_text
 */
class Notes extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'note_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'note_owner',
        'note_time',
        'note_priority',
        'note_title',
        'note_text',
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
        'note_owner' => 'int',
        'note_time' => 'int',
        'note_priority' => 'boolean',
        'note_title' => 'string',
        'note_text' => 'string',
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
