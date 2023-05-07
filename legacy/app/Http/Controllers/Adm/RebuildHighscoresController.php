<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\StatisticsLibrary as Statistics;

class RebuildHighscoresController extends BaseController
{
    private array $result = [];

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->runAction();

        Template::getInstance()->view(
            'admin.rebuildhighscores',
            $this->getStatisticsResult()
        );
    }

    private function runAction(): void
    {
        $stObject = new Statistics();
        $this->result = $stObject->makeStats();

        session()->flash('success', strtr(__('admin/rebuildhighscores.sb_stats_update'), ['%t' => $this->result['totaltime']]));

        Functions::updateConfig('stat_last_update', $this->result['stats_time']);
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
        ];
    }
}
