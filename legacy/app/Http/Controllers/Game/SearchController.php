<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\NoobsProtectionLib;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class SearchController extends BaseController
{
    use PreparesLegacySql;

    private ?NoobsProtectionLib $noob = null;
    private array $searchTerms = [
        'searchType' => '',
        'playerName' => '',
        'allianceTag' => '',
        'planetNames' => '',
        'searchText' => '',
        'errorBlock' => '',
    ];
    private array $templatesMap = [
        'playerName' => 'player_name',
        'allianceTag' => 'alliance_tag',
        'planetNames' => 'planet_names'
    ];
    private array $results = [];

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Search));

        $this->noob = new NoobsProtectionLib();

        $this->runAction();

        Template::legacyView(
            'search.view',
            array_merge(
                [
                    'searchResults' => $this->buildResultsBlock(),
                ],
                $this->searchTerms,
            )
        );
    }

    private function runAction(): void
    {
        $searchQuery = filter_input_array(INPUT_POST, [
            'searchType' => FILTER_UNSAFE_RAW,
            'searchText' => FILTER_UNSAFE_RAW,
        ]);

        $this->searchTerms['errorBlock'] = __('game/search.sh_error_empty');

        if (!empty($searchQuery['searchText'])) {
            $this->searchTerms['searchType'] = $searchQuery['searchType'];
            $this->searchTerms[$searchQuery['searchType']] = 'selected = "selected"';
            $this->searchTerms['searchText'] = $searchQuery['searchText'];

            switch ($searchQuery['searchType']) {
                case 'playerName':
                default:
                    $this->results = !empty($searchQuery['searchText']) ? array_map(
                        fn ($row) => (array) $row,
                        DB::select(
                            $this->prepareSql(
                                'SELECT
                                    u.`id`,
                                    u.`name`,
                                    u.`authlevel`,
                                    p.`planet_name`,
                                    p.`planet_galaxy`,
                                    p.`planet_system`,
                                    p.`planet_planet`,
                                    s.`user_statistic_total_rank` AS `user_rank`,
                                    a.`alliance_id`,
                                    a.`alliance_name`
                                FROM `' . USERS . '` AS u
                                    INNER JOIN `' . USERS_STATISTICS . '` AS s ON s.`user_statistic_user_id` = u.`id`
                                    INNER JOIN `' . PLANETS . '` AS p ON p.`planet_id` = u.`home_planet_id`
                                    LEFT JOIN `' . ALLIANCE . '` AS a ON a.alliance_id = u.`ally_id`
                                WHERE u.`name` LIKE ?
                                LIMIT ' . MAX_SEARCH_RESULTS . ';'
                            ),
                            ['%' . $searchQuery['searchText'] . '%']
                        )
                    ) : [];
                    break;
                case 'allianceTag':
                    $this->results = !empty($searchQuery['searchText']) ? array_map(
                        fn ($row) => (array) $row,
                        DB::select(
                            $this->prepareSql(
                                'SELECT
                                    a.`alliance_id`,
                                    a.`alliance_name`,
                                    a.`alliance_tag`,
                                    a.`alliance_request_notallow` AS `alliance_requests`,
                                    s.`alliance_statistic_total_points` AS `alliance_points`,
                                    (SELECT
                                        COUNT(id) AS `ally_members`
                                        FROM `' . USERS . '`
                                        WHERE `ally_id` = a.`alliance_id`
                                    ) AS `alliance_members`
                                FROM `' . ALLIANCE . '` AS a
                                    LEFT JOIN `' . ALLIANCE_STATISTICS . '` AS s ON a.`alliance_id` = s.`alliance_statistic_alliance_id`
                                WHERE (a.alliance_name LIKE ?)
                                    OR (a.alliance_tag LIKE ?)
                                LIMIT ' . MAX_SEARCH_RESULTS . ';'
                            ),
                            ['%' . $searchQuery['searchText'] . '%', '%' . $searchQuery['searchText'] . '%']
                        )
                    ) : [];
                    break;
                case 'planetNames':
                    $this->results = !empty($searchQuery['searchText']) ? array_map(
                        fn ($row) => (array) $row,
                        DB::select(
                            $this->prepareSql(
                                'SELECT
                                    u.`id`,
                                    u.`name`,
                                    u.`authlevel`,
                                    p.`planet_name`,
                                    p.`planet_galaxy`,
                                    p.`planet_system`,
                                    p.`planet_planet`,
                                    s.`user_statistic_total_rank` AS `user_rank`,
                                    a.`alliance_id`,
                                    a.`alliance_name`
                                FROM `' . USERS . '` AS u
                                    INNER JOIN `' . USERS_STATISTICS . '` AS s ON s.`user_statistic_user_id` = u.`id`
                                    INNER JOIN `' . PLANETS . '` AS p ON p.`planet_user_id` = u.`id`
                                    LEFT JOIN `' . ALLIANCE . '` AS a ON a.`alliance_id` = u.`ally_id`
                                WHERE p.`planet_name` LIKE ?
                                LIMIT ' . MAX_SEARCH_RESULTS . ';'
                            ),
                            ['%' . $searchQuery['searchText'] . '%']
                        )
                    ) : [];
                    break;
            }

            if (count($this->results) <= 0) {
                $this->searchTerms['errorBlock'] = __(
                    'game/search.sh_error_no_results_' . $this->templatesMap[$this->searchTerms['searchType']]
                );
            }
        }
    }

    private function buildResultsBlock(): string
    {
        if (count($this->results) > 0) {
            $this->searchTerms['errorBlock'] = '';

            return Template::render(
                'search.results.' . $this->templatesMap[$this->searchTerms['searchType']],
                array_merge(
                    [
                        'results' => $this->parseResults(),
                    ]
                )
            );
        }

        return '';
    }

    private function parseResults(): array
    {
        $resultsList = [];

        foreach ($this->results as $results) {
            if ($this->searchTerms['searchType'] == 'playerName') {
                $resultsList[] = array_merge(
                    $results,
                    [
                        'planet_position' => $this->formatService->prettyCoords((int) $results['planet_galaxy'], (int) $results['planet_system'], (int) $results['planet_planet']),
                        'user_rank' => $this->setPosition((int) $results['user_rank'], (int) $results['authlevel']),
                        'user_actions' => $this->getPlayersActions((int) $results['id']),
                    ]
                );
            }

            if ($this->searchTerms['searchType'] == 'allianceTag') {
                $resultsList[] = array_merge(
                    $results,
                    [
                        'alliance_points' => $this->formatService->prettyNumber((int) $results['alliance_points']),
                        'alliance_actions' => $this->getAllianceApplicationAction((int) $results['alliance_id'], (int) $results['alliance_requests']),
                    ]
                );
            }

            if ($this->searchTerms['searchType'] == 'planetNames') {
                $resultsList[] = array_merge(
                    $results,
                    [
                        'planet_position' => $this->formatService->prettyCoords((int) $results['planet_galaxy'], (int) $results['planet_system'], (int) $results['planet_planet']),
                        'user_rank' => $this->setPosition((int) $results['user_rank'], (int) $results['authlevel']),
                        'user_actions' => $this->getPlayersActions((int) $results['id']),
                    ]
                );
            }
        }

        return $resultsList;
    }

    private function setPosition(int $userRank, int $userLevel): string
    {
        if ($this->noob->isRankVisible($userLevel)) {
            return UrlHelper::setUrl(
                'game.php?page=statistics&start=' . $userRank,
                $this->formatService->prettyNumber((int) $userRank)
            );
        } else {
            return '-';
        }
    }

    private function getPlayersActions(int $userId): string
    {
        $chatLink = UrlHelper::setUrl(
            'game.php?page=chat&playerId=' . $userId,
            Functions::setImage(DPATH . '/img/m.gif', __('game/search.sh_tip_write')),
            __('game/search.sh_tip_apply')
        );

        $buddyLink = UrlHelper::setUrl(
            '#',
            Functions::setImage(DPATH . '/img/b.gif', __('game/search.sh_tip_buddy_request')),
            __('game/search.sh_tip_apply'),
            'onClick="f(\'game.php?page=buddies&mode=2&u=' . $userId . '\', \'' . __('game/search.sh_tip_buddy_request') . '\')"'
        );

        return $chatLink . ' ' . $buddyLink;
    }

    private function getAllianceApplicationAction(int $allianceId, int $alliance_requests): string
    {
        if ($alliance_requests == SwitchInt::on) {
            return UrlHelper::setUrl(
                'game.php?page=alliance&mode=apply&allyid=' . $allianceId,
                Functions::setImage(DPATH . '/img/m.gif', __('game/search.sh_tip_apply')),
                __('game/search.sh_tip_apply')
            );
        }

        return '';
    }
}
