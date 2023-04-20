<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;
use Xgp\App\Libraries\TimingLibrary as Timing;

class TasksController extends BaseController
{
    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage(__('adm/global.no_permissions')));
        }

        // build the page
        $this->buildPage();
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->set(
                'adm/tasks_view',
                $this->buildUpdatesBlock()
            )
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
            $task_time = Functions::readConfig($task);
            $next_run = Timing::formatExtendedDate($task_time);
            $last_run = Format::prettyTimeAgo(date('Y-m-d H:i:s', (int) $task_time)) . ' ago';
        }

        return [
            'name' => $this->langs->line('ta_' . $task),
            'next_run' => $next_run,
            'last_run' => $last_run,
            'actions' => $this->{'get' . ucwords(strtr($task, ['_' => ''])) . 'Actions'}(),
        ];
    }

    private function isTaskScheduled(string $task): bool
    {
        return !($task == 'last_backup' && Functions::readConfig('auto_backup') == 0);
    }

    private function getStatLastUpdateActions(): string
    {
        return UrlHelper::setUrl(
            'admin.php?page=rebuildhighscores',
            '<i class="fas fa-play" data-toggle="popover" data-placement="top"
            data-trigger="hover" data-content="' . $this->langs->line('ta_buildstats_title') . '"></i>',
            $this->langs->line('ta_buildstats_title')
        );
    }

    private function getLastBackupActions(): string
    {
        return UrlHelper::setUrl(
            'admin.php?page=backup',
            '<i class="fas fa-cogs" data-toggle="popover" data-placement="top"
            data-trigger="hover" data-content="' . $this->langs->line('ta_backup_title') . '"></i>',
            $this->langs->line('ta_backup_title')
        );
    }

    private function getLastCleanupActions(): string
    {
        return '';
    }
}
