<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\TimingLibrary as Timing;

class TasksController extends BaseController
{
    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        Template::legacyView(
            'admin.tasks',
            $this->buildUpdatesBlock()
        );
    }

    private function buildUpdatesBlock(): array
    {
        $update_tasks = ['stat_last_update', 'last_backup', 'last_cleanup'];
        $update_blocks = [];

        foreach ($update_tasks as $task) {
            $update_blocks[] = $this->getTaskData($task);
        }

        return ['tasks_list' => $update_blocks];
    }

    private function getTaskData(string $task): array
    {
        $next_run = '-';
        $last_run = '-';

        if ($this->isTaskScheduled($task)) {
            $task_time = Options::getInstance()->get($task);
            $next_run = Timing::formatExtendedDate($task_time);
            $last_run = Format::prettyTimeAgo(date('Y-m-d H:i:s', (int) $task_time)) . ' ago';
        }

        return [
            'name' => __('admin/tasks.ta_' . $task),
            'next_run' => $next_run,
            'last_run' => $last_run,
            'actions' => $this->{'get' . ucwords(strtr($task, ['_' => ''])) . 'Actions'}(),
        ];
    }

    private function isTaskScheduled(string $task): bool
    {
        return !($task == 'last_backup' && Options::getInstance()->get('auto_backup') == 0);
    }

    private function getStatLastUpdateActions(): string
    {
        return UrlHelper::setUrl(
            'admin.php?page=rebuildhighscores',
            '<i class="fas fa-play" data-toggle="popover" data-placement="top"
            data-trigger="hover" data-content="' . __('admin/tasks.ta_buildstats_title') . '"></i>',
            __('admin/tasks.ta_buildstats_title')
        );
    }

    private function getLastBackupActions(): string
    {
        return UrlHelper::setUrl(
            'admin.php?page=backup',
            '<i class="fas fa-cogs" data-toggle="popover" data-placement="top"
            data-trigger="hover" data-content="' . __('admin/tasks.ta_backup_title') . '"></i>',
            __('admin/tasks.ta_backup_title')
        );
    }

    private function getLastCleanupActions(): string
    {
        return '';
    }
}
