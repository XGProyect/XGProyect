<?php

declare(strict_types=1);

namespace App\View\Components\Game;

use App\Models\User;
use App\Services\SettingsService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Xgp\App\Core\Enumerators\UserRanksEnumerator;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\FormatLib;

class Leftmenu extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(private SettingsService $settingsService)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $user = User::find(session('user_id'));

        // @todo review - future reference in case there is a bug
        // $tota_rank = $this->current_user['user_statistic_total_rank'] == '' ?
        // :$this->current_planet['stats_users'] : $this->current_user['user_statistic_total_rank'];

        $menu = [];
        $modules = explode(';', $this->settingsService->getString('modules'));
        $pages = [
            ['game.php?page=overview', 'lm_overview', '', '#ffffff', false, '1', '1'],
            ['game.php?page=supplies', 'lm_resources', '', '#ffffff', false, '1', '3'],
            ['game.php?page=resourcesettings', 'lm_resources_settings', '', '#ffffff', false, '1', '4'],
            ['game.php?page=facilities', 'lm_station', '', '#ffffff', false, '1', '3'],
            ['game.php?page=traderOverview', 'lm_trader', '', '#ff8900', false, '1', '5'],
            ['game.php?page=research', 'lm_research', '', '#ffffff', false, '1', '6'],
            ['game.php?page=technologytree', 'lm_technology', '', '#ffffff', false, '1', '10'],
            ['game.php?page=shipyard', 'lm_shipyard', '', '#ffffff', false, '1', '7'],
            ['game.php?page=defenses', 'lm_defenses', '', '#ffffff', false, '1', '12'],
            ['game.php?page=fleet1', 'lm_fleet', '', '#ffffff', false, '1', '8'],
            ['game.php?page=movement', 'lm_movement', '', '#ffffff', false, '1', '9'],
            ['game.php?page=galaxy', 'lm_galaxy', '&mode=0', '#ffffff', false, '1', '11'],
            ['game.php?page=empire', 'lm_empire', '', '#ffffff', false, '1', '2'],
            ['game.php?page=alliance', 'lm_alliance', '', '#ffffff', false, '1', '13'],
            ['game.php?page=premium', 'lm_officiers', '', '#ff8900', false, '1', '15'],
            ['game.php?page=messages', 'lm_messages', '', '#ffffff', false, '1', '18'],
            ['game.php?page=highscore', 'lm_statistics', '&range=' . $user->stats->user_statistic_total_rank, '#ffffff', false, '2', '16'],
            ['game.php?page=notices', 'lm_notes', '', '#ffffff', true, '2', '19'],
            ['game.php?page=buddies', 'lm_buddylist', '', '#ffffff', false, '2', '20'],
            ['game.php?page=search', 'lm_search', '', '#ffffff', false, '2', '17'],
            ['game.php?page=preferences', 'lm_options', '', '#ffffff', false, '2', '21'],
            ['game.php?page=logout', 'lm_logout', '', '#ffffff', false, '2', ''],
            [$this->settingsService->getString('forum_url'), 'lm_forums', '', '#ffffff', false, '3', '14'],
        ];
        $blocks = [
            '1' => ['ogame-produktion.jpg', '110', '40'],
            '2' => ['info-help.jpg', '110', '19'],
            '3' => ['user-menu.jpg', '110', '35'],
        ];

        // remove not enabled pages
        $pages = array_filter($pages, function ($page) use ($modules) {
            return (!isset($modules[$page[6]]) || ($modules[$page[6]] === '1' || $modules[$page[6]] === ''));
        });

        // build the menu array
        foreach ($pages as $page) {
            $menu[$page[5]][] = [
                'link' => UrlHelper::setUrl(
                    ($page[4] ? '' : ($page[0] . (!empty($page[2]) ? $page[2] : ''))),
                    FormatLib::spanStyleElement(__('game/menu.' . $page[1]), 'color: ' . $page[3] . ';'),
                    __('game/menu.' . $page[1]),
                    ($page[4] ? 'onClick="f(\'' . $page[0] . '\', \'' . __('game/menu.' . $page[1]) . '\')"' : '')
                ),
            ];
        }

        return view(
            'components.game.leftmenu',
            [
                'userName' => UrlHelper::setUrl('game.php?page=preferences', $user->name),
                'blocks' => $blocks,
                'menu' => $menu,
                'isAdmin' => $user->authlevel > UserRanksEnumerator::PLAYER,
                'servername' => $this->settingsService->getString('game_name'),
                'changelog' => UrlHelper::setUrl('game.php?page=changelog', config('version.files')),
            ]
        );
    }
}
