<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\StatisticsLibrary as Statistics;

class RebuildHighscoresController extends BaseController
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function index(): View
    {
        return view('admin.rebuildhighscores');
    }

    public function run(): RedirectResponse
    {
        $result = (new Statistics())->makeStats();

        session()->flash('success', strtr(__('admin/rebuildhighscores.sb_stats_update'), ['%t' => $result['totaltime']]));

        $this->settings->write('stat_last_update', $result['stats_time']);

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
