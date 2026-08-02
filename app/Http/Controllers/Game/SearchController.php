<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Enums\SearchType;
use App\Http\Requests\Game\SearchRequest;
use App\Models\Alliance;
use App\Models\User;
use App\Services\FormatService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class SearchController extends BaseController
{
    private const MAX_RESULTS = 25;
    private const ALLIANCE_REQUESTS_OPEN = 1;

    public function __construct(
        private FormatService $formatService,
        private SettingsService $settings,
    ) {
    }

    public function __invoke(SearchRequest $request): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Search));

        $type = $request->searchType();
        $text = $request->searchText();
        $submitted = $request->wasSubmitted() && $text !== '';

        $rows = $submitted ? $this->runSearch($type, $text) : collect();
        $errorBlock = $this->resolveErrorMessage($submitted, $rows, $type);

        return view('search.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'searchType' => $type->value,
            'searchText' => $text,
            'playerName' => $type === SearchType::PlayerName ? ' selected="selected"' : '',
            'allianceTag' => $type === SearchType::AllianceTag ? ' selected="selected"' : '',
            'planetNames' => $type === SearchType::PlanetName ? ' selected="selected"' : '',
            'errorBlock' => $errorBlock,
            'searchResults' => $rows->isEmpty() ? '' : $this->renderResults($type, $rows->all()),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function runSearch(SearchType $type, string $text): Collection
    {
        $like = '%' . $text . '%';

        return match ($type) {
            SearchType::PlayerName => $this->playerResults(fn ($q) => $q->where('users.name', 'like', $like)),
            SearchType::PlanetName => $this->planetResults($like),
            SearchType::AllianceTag => $this->allianceResults($like),
        };
    }

    /**
     * Players whose name matches. Joined to home planet (planets.planet_id = users.home_planet_id).
     *
     * @param \Closure(\Illuminate\Database\Eloquent\Builder<User>): \Illuminate\Database\Eloquent\Builder<User> $filter
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function playerResults(\Closure $filter): Collection
    {
        $query = $this->basePlayerQuery();
        $query = $filter($query);

        return $query->limit(self::MAX_RESULTS)->get()->map(fn ($row) => $this->mapPlayerRow($row));
    }

    /**
     * Players whose any planet name matches (returns one row per matching planet,
     * matching the legacy behaviour of joining planets by owner instead of home).
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function planetResults(string $like): Collection
    {
        $rows = User::query()
            ->join('users_statistics', 'users_statistics.user_statistic_user_id', '=', 'users.id')
            ->join('planets', 'planets.planet_user_id', '=', 'users.id')
            ->leftJoin('alliance', 'alliance.alliance_id', '=', 'users.ally_id')
            ->where('planets.planet_name', 'like', $like)
            ->limit(self::MAX_RESULTS)
            ->get([
                'users.id',
                'users.name',
                'users.authlevel',
                'planets.planet_name',
                'planets.planet_galaxy',
                'planets.planet_system',
                'planets.planet_planet',
                'users_statistics.user_statistic_total_rank as user_rank',
                'alliance.alliance_id',
                'alliance.alliance_name',
            ]);

        return $rows->map(fn ($row) => $this->mapPlayerRow($row));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function basePlayerQuery()
    {
        return User::query()
            ->join('users_statistics', 'users_statistics.user_statistic_user_id', '=', 'users.id')
            ->join('planets', 'planets.planet_id', '=', 'users.home_planet_id')
            ->leftJoin('alliance', 'alliance.alliance_id', '=', 'users.ally_id')
            ->select([
                'users.id',
                'users.name',
                'users.authlevel',
                'planets.planet_name',
                'planets.planet_galaxy',
                'planets.planet_system',
                'planets.planet_planet',
                'users_statistics.user_statistic_total_rank as user_rank',
                'alliance.alliance_id',
                'alliance.alliance_name',
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function allianceResults(string $like): Collection
    {
        $memberCount = DB::table('users')
            ->selectRaw('COUNT(id)')
            ->whereColumn('users.ally_id', 'alliance.alliance_id');

        $rows = Alliance::query()
            ->leftJoin('alliance_statistics', 'alliance.alliance_id', '=', 'alliance_statistics.alliance_statistic_alliance_id')
            ->where(function ($q) use ($like): void {
                $q->where('alliance.alliance_name', 'like', $like)
                    ->orWhere('alliance.alliance_tag', 'like', $like);
            })
            ->selectSub($memberCount, 'alliance_members')
            ->select([
                'alliance.alliance_id',
                'alliance.alliance_name',
                'alliance.alliance_tag',
                'alliance.alliance_request_notallow as alliance_requests',
                'alliance_statistics.alliance_statistic_total_points as alliance_points',
            ])
            ->limit(self::MAX_RESULTS)
            ->get();

        return $rows->map(fn ($row) => [
            'alliance_id' => (int) $row->alliance_id,
            'alliance_name' => (string) $row->alliance_name,
            'alliance_tag' => (string) $row->alliance_tag,
            'alliance_members' => (int) $row->alliance_members,
            'alliance_points' => $this->formatService->prettyNumber((int) $row->alliance_points),
            'alliance_actions' => $this->allianceApplyAction(
                (int) $row->alliance_id,
                (int) $row->alliance_requests,
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPlayerRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'planet_name' => (string) $row->planet_name,
            'planet_galaxy' => (int) $row->planet_galaxy,
            'planet_system' => (int) $row->planet_system,
            'planet_planet' => (int) $row->planet_planet,
            'planet_position' => $this->formatService->prettyCoords(
                (int) $row->planet_galaxy,
                (int) $row->planet_system,
                (int) $row->planet_planet,
            ),
            'alliance_id' => (int) ($row->alliance_id ?? 0),
            'alliance_name' => (string) ($row->alliance_name ?? ''),
            'user_rank' => $this->rankCell((int) $row->user_rank, (int) $row->authlevel),
            'user_actions' => $this->playerActions((int) $row->id),
        ];
    }

    /**
     * Hides ranks of users above the admin cut-off (replaces NoobsProtectionLib::isRankVisible).
     */
    private function rankCell(int $userRank, int $userAuthLevel): string
    {
        if ($userAuthLevel > $this->settings->getInt('stat_admin_level')) {
            return '-';
        }

        return $this->formatService->link(
            'game.php?page=highscore&range=' . $userRank,
            $this->formatService->prettyNumber($userRank),
        );
    }

    private function playerActions(int $userId): string
    {
        $chat = $this->formatService->link(
            'game.php?page=chat&playerId=' . $userId,
            $this->iconImage('assets/img/m.gif', (string) __('game/search.sh_tip_write')),
            (string) __('game/search.sh_tip_write'),
        );

        $buddy = $this->formatService->link(
            '#',
            $this->iconImage('assets/img/b.gif', (string) __('game/search.sh_tip_buddy_request')),
            (string) __('game/search.sh_tip_buddy_request'),
            'onClick="f(\'game.php?page=buddies&mode=2&u=' . $userId . '\', \'' . __('game/search.sh_tip_buddy_request') . '\')"',
        );

        return $chat . ' ' . $buddy;
    }

    private function allianceApplyAction(int $allianceId, int $alliance_requests): string
    {
        if ($alliance_requests !== self::ALLIANCE_REQUESTS_OPEN) {
            return '';
        }

        return $this->formatService->link(
            'game.php?page=alliance&mode=apply&allyid=' . $allianceId,
            $this->iconImage('assets/img/m.gif', (string) __('game/search.sh_tip_apply')),
            (string) __('game/search.sh_tip_apply'),
        );
    }

    private function iconImage(string $assetPath, string $title): string
    {
        return '<img src="' . asset($assetPath) . '" alt="' . e($title) . '" title="' . e($title) . '">';
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function resolveErrorMessage(bool $submitted, Collection $rows, SearchType $type): string
    {
        if (!$submitted) {
            return (string) __('game/search.sh_error_empty');
        }

        if ($rows->isEmpty()) {
            return (string) __('game/search.sh_error_no_results_' . $type->langSlug());
        }

        return '';
    }

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private function renderResults(SearchType $type, array $results): string
    {
        return view('search.results.' . $type->langSlug(), ['results' => $results])->render();
    }
}
