<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Data\Admin\TaskData;
use App\Enums\Admin\AdminTask;
use App\Services\SettingsService;
use App\Services\TimingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class TasksService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly TimingService $timingService,
    ) {
    }

    /**
     * @return Collection<int, TaskData>
     */
    public function getTasks(): Collection
    {
        return collect(AdminTask::cases())
            ->map(fn (AdminTask $task) => $this->buildTaskData($task));
    }

    private function buildTaskData(AdminTask $task): TaskData
    {
        $nextRun = '-';
        $lastRun = '-';

        if ($this->isScheduled($task)) {
            $timestamp = (int) $this->settings->getString($task->value);
            $nextRun = $this->timingService->formatExtendedDate($timestamp);
            $lastRun = Carbon::createFromTimestamp($timestamp)->diffForHumans();
        }

        return new TaskData(
            name: $task->label(),
            nextRun: $nextRun,
            lastRun: $lastRun,
            actions: $task->actions(),
        );
    }

    private function isScheduled(AdminTask $task): bool
    {
        return $task !== AdminTask::LastBackup || $this->settings->getBool('auto_backup');
    }
}
