<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int    $alliance_id
 * @property string $alliance_name
 * @property string $alliance_tag
 * @property string $alliance_description
 * @property string $alliance_web
 * @property string $alliance_text
 * @property string $alliance_image
 * @property string $alliance_request
 * @property string $alliance_ranks
 * @property int    $alliance_owner
 * @property int    $alliance_register_time
 * @property int    $alliance_request_notallow
 */
class Alliance extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alliance';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'alliance_id';

    /**
     * Attributes that should be mass-assignable.
     *
    * @var list<string>
     */
    protected $fillable = [
        'alliance_name',
        'alliance_tag',
        'alliance_owner',
        'alliance_register_time',
        'alliance_description',
        'alliance_web',
        'alliance_text',
        'alliance_image',
        'alliance_request',
        'alliance_request_notallow',
        'alliance_ranks',
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
        'alliance_name' => 'string',
        'alliance_tag' => 'string',
        'alliance_owner' => 'int',
        'alliance_register_time' => 'int',
        'alliance_description' => 'string',
        'alliance_web' => 'string',
        'alliance_text' => 'string',
        'alliance_image' => 'string',
        'alliance_request' => 'string',
        'alliance_request_notallow' => 'int',
        'alliance_ranks' => 'string',
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
    /** @return HasMany<User, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'ally_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function owner(): BelongsTo
    {
        // @phpstan-ignore-next-line
        return $this->belongsTo(User::class, 'alliance_owner', 'id');
    }

    /** @return HasOne<AllianceStatistics, $this> */
    public function stats(): HasOne
    {
        return $this->hasOne(AllianceStatistics::class, 'alliance_statistic_alliance_id');
    }
}
