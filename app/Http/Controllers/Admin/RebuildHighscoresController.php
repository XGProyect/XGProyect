<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\StatisticsLibrary as Statistics;

class RebuildHighscoresController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.rebuildhighscores');
    }

    public function run(): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $result = (new Statistics())->makeStats();

        session()->flash('success', strtr(__('admin/rebuildhighscores.sb_stats_update'), ['%t' => $result['totaltime']]));

        Options::getInstance()->write('stat_last_update', $result['stats_time']);

        return redirect()->route('admin.rebuildhighscores')->with([
            'memory_p' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($result['memory_peak'][0]),
                '%m' => Format::prettyBytes($result['memory_peak'][0]),
            ]),
            'memory_i' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($result['initial_memory'][0]),
                '%m' => Format::prettyBytes($result['initial_memory'][0]),
            ]),
            'memory_e' => strtr('%i / %m', [
                '%i' => Format::prettyBytes($result['end_memory'][0]),
                '%m' => Format::prettyBytes($result['end_memory'][0]),
            ]),
        ]);
    }
}
