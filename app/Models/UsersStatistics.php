<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasHighscoreColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int   $user_statistic_user_id
 * @property int   $user_statistic_buildings_old_rank
 * @property int   $user_statistic_buildings_rank
 * @property int   $user_statistic_defenses_old_rank
 * @property int   $user_statistic_defenses_rank
 * @property int   $user_statistic_ships_old_rank
 * @property int   $user_statistic_ships_rank
 * @property int   $user_statistic_military_old_rank
 * @property int   $user_statistic_military_rank
 * @property int   $user_statistic_technology_old_rank
 * @property int   $user_statistic_technology_rank
 * @property int   $user_statistic_total_old_rank
 * @property int   $user_statistic_total_rank
 * @property int   $user_statistic_update_time
 * @property float $user_statistic_buildings_points
 * @property float $user_statistic_defenses_points
 * @property float $user_statistic_ships_points
 * @property float $user_statistic_military_points
 * @property float $user_statistic_technology_points
 * @property float $user_statistic_total_points
 */
class UsersStatistics extends Model
{
    use HasHighscoreColumns;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_statistics';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_statistic_user_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_statistic_buildings_points',
        'user_statistic_buildings_old_rank',
        'user_statistic_buildings_rank',
        'user_statistic_defenses_points',
        'user_statistic_defenses_old_rank',
        'user_statistic_defenses_rank',
        'user_statistic_ships_points',
        'user_statistic_ships_old_rank',
        'user_statistic_ships_rank',
        'user_statistic_military_points',
        'user_statistic_military_old_rank',
        'user_statistic_military_rank',
        'user_statistic_technology_points',
        'user_statistic_technology_old_rank',
        'user_statistic_technology_rank',
        'user_statistic_total_points',
        'user_statistic_total_old_rank',
        'user_statistic_total_rank',
        'user_statistic_update_time',
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
        'user_statistic_user_id' => 'int',
        'user_statistic_buildings_points' => 'double',
        'user_statistic_buildings_old_rank' => 'int',
        'user_statistic_buildings_rank' => 'int',
        'user_statistic_defenses_points' => 'double',
        'user_statistic_defenses_old_rank' => 'int',
        'user_statistic_defenses_rank' => 'int',
        'user_statistic_ships_points' => 'double',
        'user_statistic_ships_old_rank' => 'int',
        'user_statistic_ships_rank' => 'int',
        'user_statistic_military_points' => 'double',
        'user_statistic_military_old_rank' => 'int',
        'user_statistic_military_rank' => 'int',
        'user_statistic_technology_points' => 'double',
        'user_statistic_technology_old_rank' => 'int',
        'user_statistic_technology_rank' => 'int',
        'user_statistic_total_points' => 'double',
        'user_statistic_total_old_rank' => 'int',
        'user_statistic_total_rank' => 'int',
        'user_statistic_update_time' => 'int',
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

    /**
     * Build the player ranking query for a given highscore type. Joins users +
     * alliance and exposes aliased `points`, `current_rank`, `old_rank` so the
     * caller doesn't need to know the underlying column suffix.
     *
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeRanking(Builder $query, int $type, int $statAdminLevel): Builder
    {
        $columns = self::highscoreColumnsFor($type);

        return $query
            ->join('users', 'users.id', '=', 'users_statistics.user_statistic_user_id')
            ->leftJoin('alliance', 'alliance.alliance_id', '=', 'users.ally_id')
            ->where('users.authlevel', '<=', $statAdminLevel)
            ->orderByDesc('users_statistics.user_statistic_' . $columns['points'])
            ->orderBy('users_statistics.user_statistic_total_rank')
            ->select([
                'users.id',
                'users.name',
                'users.ally_id',
                'alliance.alliance_name',
                'users_statistics.user_statistic_' . $columns['points'] . ' as points',
                'users_statistics.user_statistic_' . $columns['rank'] . ' as current_rank',
                'users_statistics.user_statistic_' . $columns['oldRank'] . ' as old_rank',
            ]);
    }

    /**
     * Count rows visible in the ranking (respects stat_admin_level cut-off).
     */
    public static function rankingCount(int $statAdminLevel): int
    {
        return User::query()->where('authlevel', '<=', $statAdminLevel)->count();
    }

    // Functions ...

    // Relations ...
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
