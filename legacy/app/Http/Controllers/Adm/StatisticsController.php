<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class StatisticsController extends BaseController
{
    public const STATISTICS_SETTINGS = [
        'stat_points' => [
            'filter' => FILTER_VALIDATE_INT,
            'options' => ['min_range' => 1],
        ],
        'stat_update_time' => [
            'filter' => FILTER_VALIDATE_INT,
            'options' => ['min_range' => 1],
        ],
        'stat_admin_level' => [
            'filter' => FILTER_VALIDATE_INT,
            'options' => ['min_range' => UserRanks::PLAYER, 'max_range' => UserRanks::ADMIN],
        ],
    ];

    private string $alert = '';
    private $user_level = 0;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        $this->runAction();
        $this->buildPage();
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::STATISTICS_SETTINGS);

        if ($data) {
            $data = array_diff($data, [null, false]);

            foreach ($data as $option => $value) {
                Functions::updateConfig($option, $value);
            }

            $this->alert = Administration::saveMessage('ok', __('admin/statistics.cs_all_ok_message'));
        }
    }

    private function buildPage(): void
    {
        Template::getInstance()->view(
            'admin.statistics_view',
            array_merge(
                $this->getStatisticsSettings(),
                $this->userLevels(),
                [
                    'alert' => $this->alert ?? '',
                ]
            )
        );
    }

    private function getStatisticsSettings(): array
    {
        return array_filter(
            Functions::readConfig('', true),
            function ($value, $key) {
                if ($key == 'stat_admin_level') {
                    $this->user_level = $value;
                }

                return array_key_exists($key, self::STATISTICS_SETTINGS);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function userLevels(): array
    {
        $user_levels = [];
        $ranks = [
            UserRanks::PLAYER,
            UserRanks::GO,
            UserRanks::SGO,
            UserRanks::ADMIN,
        ];

        foreach ($ranks as $rank_id) {
            $user_levels[] = [
                'id' => $rank_id,
                'sel' => ($this->user_level == $rank_id ? 'selected="selected"' : ''),
                'name' => $this->langs->language['user_level'][$rank_id],
            ];
        }

        return [
            'user_levels' => $user_levels,
        ];
    }
}
