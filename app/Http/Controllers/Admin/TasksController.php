<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\TimingLibrary as Timing;

class TasksController extends BaseController
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function __invoke(): View
    {
        return view('admin.tasks', $this->buildUpdatesBlock());
    }

    private function buildUpdatesBlock(): array
    {
        $tasks = ['stat_last_update', 'last_backup', 'last_cleanup'];

        return [
            'tasks_list' => array_map(fn (string $task) => $this->getTaskData($task), $tasks),
        ];
    }

    private function getTaskData(string $task): array
    {
        $nextRun = '-';
        $lastRun = '-';

        if ($this->isTaskScheduled($task)) {
            $timestamp = (int) $this->settings->getString($task);
            $nextRun = Timing::formatExtendedDate($timestamp);
            $lastRun = Carbon::createFromTimestamp($timestamp)->diffForHumans();
        }

        return [
            'name' => __('admin/tasks.ta_' . $task),
            'next_run' => $nextRun,
            'last_run' => $lastRun,
            'actions' => $this->getTaskActions($task),
        ];
    }

    private function isTaskScheduled(string $task): bool
    {
        return $task !== 'last_backup' || $this->settings->getBool('auto_backup');
    }

    /**
     * @return array<int, array{route: string, icon: string, title: string}>
     */
    private function getTaskActions(string $task): array
    {
        return match ($task) {
            'stat_last_update' => [
                [
                    'route' => 'admin.rebuildhighscores',
                    'icon' => 'fas fa-play',
                    'title' => __('admin/tasks.ta_buildstats_title'),
                ],
            ],
            'last_backup' => [
                [
                    'route' => 'admin.backup',
                    'icon' => 'fas fa-cogs',
                    'title' => __('admin/tasks.ta_backup_title'),
                ],
            ],
            default => [],
        };
    }
}
