<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;
use Xgp\App\Libraries\StatisticsLibrary as Statistics;

class RebuildHighscoresController extends BaseController
{
    private array $result = [];

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage(__('adm/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction(): void
    {
        $stObject = new Statistics();
        $this->result = $stObject->makeStats();

        Functions::updateConfig('stat_last_update', $this->result['stats_time']);
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->set(
                'adm/rebuildhighscores_view',
                $this->getStatisticsResult()
            )
        );
    }

    private function getStatisticsResult(): array
    {
        return [
            'memory_p' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($this->result['memory_peak'][0]),
                '%m' => Format::prettyBytes($this->result['memory_peak'][0]),
            ]),
            'memory_i' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($this->result['initial_memory'][0]),
                '%m' => Format::prettyBytes($this->result['initial_memory'][0]),
            ]),
            'memory_e' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($this->result['end_memory'][0]),
                '%m' => Format::prettyBytes($this->result['end_memory'][0]),
            ]),
            'alert' => Administration::saveMessage('ok', strtr(
                $this->langs->line('sb_stats_update'),
                ['%t' => $this->result['totaltime']]
            )),
        ];
    }
}
