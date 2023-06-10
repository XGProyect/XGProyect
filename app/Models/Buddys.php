<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $buddy_id
 * @property int     $buddy_sender
 * @property int     $buddy_receiver
 * @property boolean $buddy_status
 * @property string  $buddy_request_text
 */
class Buddys extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buddys';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'buddy_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'buddy_sender',
        'buddy_receiver',
        'buddy_status',
        'buddy_request_text',
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
        'buddy_id' => 'int',
        'buddy_sender' => 'int',
        'buddy_receiver' => 'int',
        'buddy_status' => 'boolean',
        'buddy_request_text' => 'string',
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
