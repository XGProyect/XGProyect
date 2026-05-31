<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Http\Requests\Game\HighscoreRequest;
use App\Models\AllianceStatistics;
use App\Models\User;
use App\Models\UsersStatistics;
use App\Services\FormatService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class HighscoreController extends BaseController
{
    private const PAGE_SIZE = 100;

    public function __construct(
        private FormatService $formatService,
        private SettingsService $settings,
    ) {
    }

    public function __invoke(HighscoreRequest $request): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Statistics));

        $who = $request->who();
        $type = $request->type();
        $range = $request->range();
        $offset = $this->offsetFor($range);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        $payload = $who === HighscoreRequest::WHO_ALLIANCE
            ? $this->allianceRows($type, $offset, $currentUser)
            : $this->playerRows($type, $offset, $currentUser);

        return view('highscore.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'who' => $who,
            'type' => $type,
            'range' => $range,
            'rows' => $payload['rows'],
            'pagination' => $this->buildPagination($payload['total'], $range),
        ]);
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    private function playerRows(int $type, int $offset, User $currentUser): array
    {
        $statAdminLevel = $this->settings->getInt('stat_admin_level');

        $total = UsersStatistics::rankingCount($statAdminLevel);

        $rows = UsersStatistics::query()
            ->ranking($type, $statAdminLevel)
            ->offset($offset)
            ->limit(self::PAGE_SIZE)
            ->get();

        $currentAllyId = (int) $currentUser->ally_id;
        $currentUserId = (int) $currentUser->id;

        $mapped = $rows->map(function ($row, int $index) use ($offset, $currentUserId, $currentAllyId) {
            $playerId = (int) $row->id;
            $isSelf = $playerId === $currentUserId;

            return [
                'position' => $offset + $index + 1,
                'rank_change' => $this->rankDelta((int) $row->old_rank, (int) $row->current_rank),
                'name' => (string) $row->name,
                'is_self' => $isSelf,
                'alliance_id' => (int) ($row->ally_id ?? 0),
                'alliance_name' => (string) ($row->alliance_name ?? ''),
                'alliance_is_mine' => $currentAllyId > 0 && (int) ($row->ally_id ?? 0) === $currentAllyId,
                'player_id' => $playerId,
                'can_message' => !$isSelf,
                'points' => $this->formatService->prettyNumber((int) $row->points),
            ];
        });

        return ['rows' => $mapped->all(), 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    private function allianceRows(int $type, int $offset, User $currentUser): array
    {
        $total = AllianceStatistics::rankingCount();

        $rows = AllianceStatistics::query()
            ->ranking($type)
            ->offset($offset)
            ->limit(self::PAGE_SIZE)
            ->get();

        $currentAllyId = (int) $currentUser->ally_id;

        $mapped = $rows->map(function ($row, int $index) use ($offset, $currentAllyId) {
            $members = max(1, (int) $row->member_count);
            $points = (int) $row->points;

            return [
                'position' => $offset + $index + 1,
                'rank_change' => $this->rankDelta((int) $row->old_rank, (int) $row->current_rank),
                'alliance_id' => (int) $row->alliance_id,
                'alliance_name' => (string) $row->alliance_name,
                'alliance_tag' => strtoupper((string) $row->alliance_tag),
                'alliance_is_mine' => $currentAllyId > 0 && (int) $row->alliance_id === $currentAllyId,
                'can_request' => (int) $row->alliance_request_notallow === 1,
                'members' => (int) $row->member_count,
                'points' => $this->formatService->prettyNumber($points),
                'points_per_member' => $this->formatService->prettyNumber((int) floor($points / $members)),
            ];
        });

        return ['rows' => $mapped->all(), 'total' => $total];
    }

    /**
     * @return array{
     *     current_page: int,
     *     total_pages: int,
     *     pages: list<array{value: int, label: string, active: bool}>
     * }
     */
    private function buildPagination(int $total, int $range): array
    {
        $totalPages = (int) max(1, ceil($total / self::PAGE_SIZE));
        $currentPage = (int) min($totalPages, max(1, (int) ceil($range / self::PAGE_SIZE)));

        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $start = ($page - 1) * self::PAGE_SIZE + 1;
            $end = $page * self::PAGE_SIZE;
            $pages[] = [
                'value' => $start,
                'label' => $start . '-' . $end,
                'active' => $page === $currentPage,
            ];
        }

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'pages' => $pages,
        ];
    }

    private function offsetFor(int $range): int
    {
        return (int) (($range - 1) / self::PAGE_SIZE) * self::PAGE_SIZE;
    }

    /**
     * @return array{delta: int, label: string}
     */
    private function rankDelta(int $oldRank, int $currentRank): array
    {
        $delta = $oldRank - $currentRank;

        return [
            'delta' => $delta,
            'label' => $delta > 0 ? '+' . $delta : (string) $delta,
        ];
    }
}
