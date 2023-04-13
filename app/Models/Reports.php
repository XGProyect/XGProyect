<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $report_rid
 * @property string $report_owners
 * @property string $report_content
 * @property int    $report_destroyed
 * @property int    $report_time
 */
class Reports extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'report_rid';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'report_owners', 'report_content', 'report_destroyed', 'report_time'
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
        'report_rid' => 'string', 'report_owners' => 'string', 'report_content' => 'string', 'report_destroyed' => 'int', 'report_time' => 'int'
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
