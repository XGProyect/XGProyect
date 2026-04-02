<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $premium_user_id
 * @property int $premium_dark_matter
 * @property int $premium_officier_commander
 * @property int $premium_officier_admiral
 * @property int $premium_officier_engineer
 * @property int $premium_officier_geologist
 * @property int $premium_officier_technocrat
 */
class Premium extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'premium';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'premium_user_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'premium_dark_matter',
        'premium_officier_commander',
        'premium_officier_admiral',
        'premium_officier_engineer',
        'premium_officier_geologist',
        'premium_officier_technocrat',
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
        'premium_user_id' => 'int',
        'premium_dark_matter' => 'int',
        'premium_officier_commander' => 'int',
        'premium_officier_admiral' => 'int',
        'premium_officier_engineer' => 'int',
        'premium_officier_geologist' => 'int',
        'premium_officier_technocrat' => 'int',
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
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
