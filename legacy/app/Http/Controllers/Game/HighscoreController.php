<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class HighscoreController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Statistics));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->buildPage();
    }

    private function buildPage(): void
    {
        $who = (isset($_POST['who'])) ? $_POST['who'] : ((isset($_GET['who'])) ? $_GET['who'] : 1);
        $type = (isset($_POST['type'])) ? $_POST['type'] : ((isset($_GET['type'])) ? $_GET['type'] : 1);
        $range = (isset($_POST['range'])) ? $_POST['range'] : ((isset($_GET['range'])) ? $_GET['range'] : 1);
        $type = is_scalar($type) ? (string) $type : '1';

        if ($type === '5') {
            $type = '4';
        }

        $parse['who'] = '<option value="1"' . (($who == '1') ? ' SELECTED' : '') . '>' . __('game/highscore.st_player') . '</option>';
        $parse['who'] .= '<option value="2"' . (($who == '2') ? ' SELECTED' : '') . '>' . __('game/highscore.st_alliance') . '</option>';

        $parse['type'] = '<option value="1"' . (($type == '1') ? ' SELECTED' : '') . '>' . __('game/highscore.st_total') . '</option>';
        $parse['type'] .= '<option value="2"' . (($type == '2') ? ' SELECTED' : '') . '>' . __('game/highscore.st_economy') . '</option>';
        $parse['type'] .= '<option value="3"' . (($type == '3') ? ' SELECTED' : '') . '>' . __('game/highscore.st_research') . '</option>';
        $parse['type'] .= '<option value="4"' . (($type == '4') ? ' SELECTED' : '') . '>' . __('game/highscore.st_military') . '</option>';

        $data = $this->ranking_type($type);
        $Order = $data['order'];
        $Points = $data['points'];
        $Rank = $data['rank'];
        $OldRank = $data['oldrank'];

        if ($who == 2) {
            $countRow = DB::selectOne($this->prepareSql('SELECT COUNT(`alliance_id`) AS `count` FROM `' . ALLIANCE . '`;'));
            $MaxAllys = $countRow !== null ? (int) $countRow->count : 0;

            $parse['range'] = $this->build_range_list($MaxAllys, $range);
            $parse['stat_header'] = Template::render(
                'highscore.alliance_header',
                $parse
            );

            $start = floor(intval($range / 100) % 100) * 100;
            $query = array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            s.*,
                            a.`alliance_id`,
                            a.`alliance_tag`,
                            a.`alliance_name`,
                            a.`alliance_request_notallow`,
                            (
                                SELECT
                                    COUNT(id) AS `ally_members`
                                FROM `' . USERS . '`
                                WHERE `ally_id` = a.`alliance_id`
                            ) AS `ally_members`
                        FROM `' . ALLIANCE_STATISTICS . '` AS s
                        INNER JOIN  `' . ALLIANCE . '` AS a ON a.`alliance_id` = s.`alliance_statistic_alliance_id`
                        ORDER BY `alliance_statistic_' . $Order . '` DESC, `alliance_statistic_total_rank` ASC
                        LIMIT ' . $start . ',100;'
                    )
                )
            );

            $start++;

            $parse['stat_values'] = '';

            foreach ($query as $StatRow) {
                $parse['ally_rank'] = $start;
                $ranking = $StatRow['alliance_statistic_' . $OldRank] - $StatRow['alliance_statistic_' . $Rank];
                $parse['ally_rankplus'] = $this->rank_difference($ranking);
                $parse['ally_id'] = $StatRow['alliance_id'];
                $parse['alliance_name'] = $StatRow['alliance_name'];
                $parse['ally_members'] = $StatRow['ally_members'];
                $parse['ally_action'] = $StatRow['alliance_request_notallow'] == 1 ? '<a href="game.php?page=alliance&mode=apply&allyid=' . $StatRow['alliance_id'] . '"><img src="' . DPATH . 'img/m.gif" border="0" title="' . __('game/statistics.st_ally_request') . '" /></a>' : '';
                $parse['ally_points'] = $this->formatService->prettyNumber((int) $StatRow['alliance_statistic_' . $Order]);
                $parse['ally_members_points'] = $this->formatService->prettyNumber(floor($StatRow['alliance_statistic_' . $Order] / $StatRow['ally_members']));
                $parse['stat_values'] .= Template::render(
                    'highscore.alliance_table',
                    $parse
                );

                $start++;
            }
        } else {
            $parse['range'] = $this->build_range_list($this->planet['stats_users'], $range);
            $parse['stat_header'] = Template::render(
                'highscore.player_header',
                $parse
            );

            $start = floor(intval($range / 100) % 100) * 100;
            $query = array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            s.*,
                            u.`id`,
                            u.`name`,
                            u.`ally_id`,
                            a.`alliance_name`
                        FROM `' . USERS_STATISTICS . '` as s
                        INNER JOIN `' . USERS . '` as u ON u.`id` = s.`user_statistic_user_id`
                        LEFT JOIN `' . ALLIANCE . '` AS a ON a.`alliance_id` = u.`ally_id`
                        WHERE `authlevel` <= ' . app(SettingsService::class)->getInt('stat_admin_level') . '
                        ORDER BY `user_statistic_' . $Order . '` DESC, `user_statistic_total_rank` ASC
                        LIMIT ' . $start . ',100;'
                    )
                )
            );

            $start++;
            $parse['stat_values'] = '';
            $previusId = 0;

            foreach ($query as $StatRow) {
                $parse['player_rank'] = $start;
                $ranking = $StatRow['user_statistic_' . $OldRank] - $StatRow['user_statistic_' . $Rank];

                if ($StatRow['id'] == $this->user['id']) {
                    $parse['player_name'] = '<font color="lime">' . $StatRow['name'] . '</font>';
                } else {
                    $parse['player_name'] = $StatRow['name'];
                }

                if ($StatRow['id'] != $this->user['id']) {
                    $parse['player_mes'] = '<a href="game.php?page=chat&playerId=' . $StatRow['id'] . '"><img src="' . DPATH . 'img/m.gif" border="0" title="' . __('game/global.write_message') . '" /></a>';
                } else {
                    $parse['player_mes'] = '';
                }

                if ($StatRow['alliance_name'] != '') {
                    if ($StatRow['alliance_name'] == $this->user['alliance_name']) {
                        $parse['player_alliance'] = '<a href="game.php?page=alliance&mode=ainfo&allyid=' . $StatRow['ally_id'] . '"><font color="#33CCFF">[' . $StatRow['alliance_name'] . ']</font></a>';
                    } else {
                        $parse['player_alliance'] = '<a href="game.php?page=alliance&mode=ainfo&allyid=' . $StatRow['ally_id'] . '">[' . $StatRow['alliance_name'] . ']</a>';
                    }
                } else {
                    $parse['player_alliance'] = '';
                }

                $parse['player_rankplus'] = $this->rank_difference($ranking);
                $parse['player_points'] = $this->formatService->prettyNumber((int) $StatRow['user_statistic_' . $Order]);
                $parse['stat_values'] .= Template::render(
                    'highscore.player_table',
                    $parse
                );
                $start++;
            }
        }

        Template::legacyView(
            'highscore.body',
            $parse
        );
    }

    /**
     * method rank_difference
     * param $ranking
     * return return the rank difference between update and update and returns it formated
     */
    private function rank_difference($ranking)
    {
        if ($ranking == 0) {
            return '<font color="#87CEEB">*</font>';
        }

        if ($ranking < 0) {
            return '<font color="red">' . $ranking . '</font>';
        }

        if ($ranking > 0) {
            return '<font color="green">+' . $ranking . '</font>';
        }
    }

    /**
     * method build_range_list
     * param $count
     * param $range
     * return the list of range values
     */
    private function build_range_list($count, $range)
    {
        $range_list = '';
        $last_page = 0;

        // SET LAST PAGE
        if ($count > 100) {
            $last_page = floor($count / 100);
        }

        // LOOP TO BUILD THE VALUES LIST
        for ($page = 0; $page <= $last_page; $page++) {
            $page_value = $page * 100 + 1;
            $page_range = $page_value + 99;
            $range_list .= '<option value="' . $page_value . '"' . (($range >= $page_value && $range <= $page_range) ? ' SELECTED' : '') . '>' . $page_value . '-' . $page_range . '</option>';
        }

        return $range_list; // RETURN THE LIST
    }

    /**
     * method ranking_type
     * param $type
     * return the configurations or values for the current statistics type
     */
    private function ranking_type($type)
    {
        // SWITCH TYPE
        switch ($type) {
            case 1: // TOTAL POINTS
            default:
                $return['order'] = 'total_points';
                $return['points'] = 'total_points';
                $return['rank'] = 'total_rank';
                $return['oldrank'] = 'total_old_rank';
                break;

            case 2: // ECONOMY
                $return['order'] = 'buildings_points';
                $return['points'] = 'buildings_points';
                $return['rank'] = 'buildings_rank';
                $return['oldrank'] = 'buildings_old_rank';
                break;

            case 3: // RESEARCH
                $return['order'] = 'technology_points';
                $return['points'] = 'technology_points';
                $return['rank'] = 'technology_rank';
                $return['oldrank'] = 'technology_old_rank';
                break;

            case 4: // MILITARY
            case 5: // Backward compatibility for the old defense filter.
                $return['order'] = 'military_points';
                $return['points'] = 'military_points';
                $return['rank'] = 'military_rank';
                $return['oldrank'] = 'military_old_rank';
                break;
        }

        return $return;
    }
}
