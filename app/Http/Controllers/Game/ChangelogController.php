<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Changelog;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\Functions;

class ChangelogController extends BaseController
{
    public function __construct(
        private SettingsService $settings,
        private TimingService $timingService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Changelog));

        return view('changelog.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'changes' => Changelog::query()
                ->join('languages', 'languages.id', '=', 'changelog.changelog_lang_id')
                ->where('languages.code', $this->settings->getString('lang'))
                ->orderByDesc('changelog_date')
                ->get(['changelog_version', 'changelog_date', 'changelog_description'])
                ->map(fn ($entry) => [
                    'versionNumber' => $entry->changelog_version,
                    'description' => nl2br(
                        $this->timingService->formatShortDate($entry->changelog_date->timestamp)
                        . '<br>'
                        . $entry->changelog_description
                    ),
                ]),
        ]);
    }
}
