<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;

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

    private $user_level = 0;
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.statistics',
            array_merge(
                $this->getStatisticsSettings(),
                $this->userLevels()
            )
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::STATISTICS_SETTINGS);

        if ($data) {
            $data = array_diff($data, [null, false]);

            foreach ($data as $option => $value) {
                Options::getInstance()->write($option, $value);
            }

            session()->flash('success', __('admin/statistics.cs_all_ok_message'));
        }
    }

    private function getStatisticsSettings(): array
    {
        return array_filter(
            Options::getInstance()->get(),
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
                'name' => __('admin/global.user_level')[$rank_id],
            ];
        }

        return [
            'user_levels' => $user_levels,
        ];
    }
}
