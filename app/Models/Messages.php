<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $message_sender
 * @property int    $message_receiver
 * @property int    $message_time
 * @property int    $message_type
 * @property int    $message_read
 * @property string $message_from
 * @property string $message_subject
 * @property string $message_text
 */
class Messages extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_sender', 'message_receiver', 'message_time', 'message_type', 'message_from', 'message_subject', 'message_text', 'message_read'
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
        'message_sender' => 'int', 'message_receiver' => 'int', 'message_time' => 'int', 'message_type' => 'int', 'message_from' => 'string', 'message_subject' => 'string', 'message_text' => 'string', 'message_read' => 'int'
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
