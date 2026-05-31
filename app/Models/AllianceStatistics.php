<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasHighscoreColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int   $alliance_statistic_alliance_id
 * @property int   $alliance_statistic_buildings_old_rank
 * @property int   $alliance_statistic_buildings_rank
 * @property int   $alliance_statistic_defenses_old_rank
 * @property int   $alliance_statistic_defenses_rank
 * @property int   $alliance_statistic_ships_old_rank
 * @property int   $alliance_statistic_ships_rank
 * @property int   $alliance_statistic_military_old_rank
 * @property int   $alliance_statistic_military_rank
 * @property int   $alliance_statistic_technology_old_rank
 * @property int   $alliance_statistic_technology_rank
 * @property int   $alliance_statistic_total_old_rank
 * @property int   $alliance_statistic_total_rank
 * @property int   $alliance_statistic_update_time
 * @property float $alliance_statistic_buildings_points
 * @property float $alliance_statistic_defenses_points
 * @property float $alliance_statistic_ships_points
 * @property float $alliance_statistic_military_points
 * @property float $alliance_statistic_technology_points
 * @property float $alliance_statistic_total_points
 */
class AllianceStatistics extends Model
{
    use HasHighscoreColumns;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alliance_statistics';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'alliance_statistic_alliance_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'alliance_statistic_buildings_points',
        'alliance_statistic_buildings_old_rank',
        'alliance_statistic_buildings_rank',
        'alliance_statistic_defenses_points',
        'alliance_statistic_defenses_old_rank',
        'alliance_statistic_defenses_rank',
        'alliance_statistic_ships_points',
        'alliance_statistic_ships_old_rank',
        'alliance_statistic_ships_rank',
        'alliance_statistic_military_points',
        'alliance_statistic_military_old_rank',
        'alliance_statistic_military_rank',
        'alliance_statistic_technology_points',
        'alliance_statistic_technology_old_rank',
        'alliance_statistic_technology_rank',
        'alliance_statistic_total_points',
        'alliance_statistic_total_old_rank',
        'alliance_statistic_total_rank',
        'alliance_statistic_update_time',
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
        'alliance_statistic_alliance_id' => 'int',
        'alliance_statistic_buildings_points' => 'double',
        'alliance_statistic_buildings_old_rank' => 'int',
        'alliance_statistic_buildings_rank' => 'int',
        'alliance_statistic_defenses_points' => 'double',
        'alliance_statistic_defenses_old_rank' => 'int',
        'alliance_statistic_defenses_rank' => 'int',
        'alliance_statistic_ships_points' => 'double',
        'alliance_statistic_ships_old_rank' => 'int',
        'alliance_statistic_ships_rank' => 'int',
        'alliance_statistic_military_points' => 'double',
        'alliance_statistic_military_old_rank' => 'int',
        'alliance_statistic_military_rank' => 'int',
        'alliance_statistic_technology_points' => 'double',
        'alliance_statistic_technology_old_rank' => 'int',
        'alliance_statistic_technology_rank' => 'int',
        'alliance_statistic_total_points' => 'double',
        'alliance_statistic_total_old_rank' => 'int',
        'alliance_statistic_total_rank' => 'int',
        'alliance_statistic_update_time' => 'int',
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
     * Build the alliance ranking query for a given highscore type. Joins the
     * alliance table, exposes the member count via a correlated subquery, and
     * aliases `points`, `current_rank`, `old_rank` so the controller is free
     * of column-suffix knowledge.
     *
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeRanking(Builder $query, int $type): Builder
    {
        $columns = self::highscoreColumnsFor($type);

        $memberCount = User::query()
            ->selectRaw('COUNT(id)')
            ->whereColumn('users.ally_id', 'alliance.alliance_id');

        return $query
            ->join('alliance', 'alliance.alliance_id', '=', 'alliance_statistics.alliance_statistic_alliance_id')
            ->orderByDesc('alliance_statistics.alliance_statistic_' . $columns['points'])
            ->orderBy('alliance_statistics.alliance_statistic_total_rank')
            ->select([
                'alliance.alliance_id',
                'alliance.alliance_name',
                'alliance.alliance_tag',
                'alliance.alliance_request_notallow',
                'alliance_statistics.alliance_statistic_' . $columns['points'] . ' as points',
                'alliance_statistics.alliance_statistic_' . $columns['rank'] . ' as current_rank',
                'alliance_statistics.alliance_statistic_' . $columns['oldRank'] . ' as old_rank',
            ])
            ->selectSub($memberCount, 'member_count');
    }

    public static function rankingCount(): int
    {
        return Alliance::query()->count();
    }

    // Functions ...

    // Relations ...
}
