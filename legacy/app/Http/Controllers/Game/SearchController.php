<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\NoobsProtectionLib;
use Xgp\App\Models\Game\Search;

class SearchController extends BaseController
{
    public const MODULE_ID = 17;

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
    private Search $searchModel;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->noob = new NoobsProtectionLib();
        $this->searchModel = new Search();

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
                    $this->results = $this->searchModel->getResultsByPlayerName($searchQuery['searchText']);
                    break;
                case 'allianceTag':
                    $this->results = $this->searchModel->getResultsByAllianceTag($searchQuery['searchText']);
                    break;
                case 'planetNames':
                    $this->results = $this->searchModel->getResultsByPlanetName($searchQuery['searchText']);
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

            return Template::getInstance()->render(
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
                        'planet_position' => FormatLib::prettyCoords((int) $results['planet_galaxy'], (int) $results['planet_system'], (int) $results['planet_planet']),
                        'user_rank' => $this->setPosition((int) $results['user_rank'], (int) $results['authlevel']),
                        'user_actions' => $this->getPlayersActions((int) $results['id']),
                    ]
                );
            }

            if ($this->searchTerms['searchType'] == 'allianceTag') {
                $resultsList[] = array_merge(
                    $results,
                    [
                        'alliance_points' => FormatLib::prettyNumber($results['alliance_points']),
                        'alliance_actions' => $this->getAllianceApplicationAction((int) $results['alliance_id'], (int) $results['alliance_requests']),
                    ]
                );
            }

            if ($this->searchTerms['searchType'] == 'planetNames') {
                $resultsList[] = array_merge(
                    $results,
                    [
                        'planet_position' => FormatLib::prettyCoords((int) $results['planet_galaxy'], (int) $results['planet_system'], (int) $results['planet_planet']),
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
                FormatLib::prettyNumber($userRank)
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
