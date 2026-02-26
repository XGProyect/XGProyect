<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\StatisticsLibrary as Statistics;

class RebuildHighscoresController extends BaseController
{
    private array $result = [];
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
            'admin.rebuildhighscores',
            $this->getStatisticsResult()
        );
    }

    private function runAction(): void
    {
        $stObject = new Statistics();
        $this->result = $stObject->makeStats();

        session()->flash('success', strtr(__('admin/rebuildhighscores.sb_stats_update'), ['%t' => $this->result['totaltime']]));

        Options::getInstance()->write('stat_last_update', $this->result['stats_time']);
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
