<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $admin_id
 * @property string $details
 * @property \Carbon\Carbon $until
 * @property User   $user
 * @property User   $admin
 */
class Ban extends Model
{
    /**
     * Attributes that should be mass-assignable.
     *
    * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'admin_id',
        'details',
        'until',
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
        'id' => 'int',
        'user_id' => 'int',
        'admin_id' => 'int',
        'details' => 'string',
        'until' => 'timestamp',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
    * @var list<string>
     */
    protected $dates = [
        'until',
    ];

    // Scopes...

    // Functions ...

    // Relations ...
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }
}
