<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Http\Requests\Game\HighscoreRequest;
use InvalidArgumentException;

/**
 * Shared mapping between the request "type" code (1-4) and the actual column
 * suffix used by xgp_users_statistics / xgp_alliance_statistics. Centralising it
 * here keeps the controller out of the schema details and avoids duplicating
 * the switch on both stat models.
 */
trait HasHighscoreColumns
{
    /**
     * @return array{points: string, rank: string, oldRank: string}
     */
    public static function highscoreColumnsFor(int $type): array
    {
        return match ($type) {
            HighscoreRequest::TYPE_ECONOMY => [
                'points' => 'buildings_points',
                'rank' => 'buildings_rank',
                'oldRank' => 'buildings_old_rank',
            ],
            HighscoreRequest::TYPE_RESEARCH => [
                'points' => 'technology_points',
                'rank' => 'technology_rank',
                'oldRank' => 'technology_old_rank',
            ],
            HighscoreRequest::TYPE_MILITARY => [
                'points' => 'military_points',
                'rank' => 'military_rank',
                'oldRank' => 'military_old_rank',
            ],
            HighscoreRequest::TYPE_TOTAL => [
                'points' => 'total_points',
                'rank' => 'total_rank',
                'oldRank' => 'total_old_rank',
            ],
            default => throw new InvalidArgumentException("Unknown highscore type: {$type}"),
        };
    }
}
