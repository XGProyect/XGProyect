<?php

declare(strict_types=1);

namespace App\Enums\Admin;

use App\Data\Admin\TaskActionData;

enum AdminTask: string
{
    case StatLastUpdate = 'stat_last_update';
    case LastBackup = 'last_backup';
    case LastCleanup = 'last_cleanup';

    public function label(): string
    {
        return __('admin/tasks.ta_' . $this->value);
    }

    /**
     * @return array<int, TaskActionData>
     */
    public function actions(): array
    {
        return match ($this) {
            self::StatLastUpdate => [
                new TaskActionData(
                    route: 'admin.rebuildhighscores',
                    icon: 'fas fa-play',
                    title: __('admin/tasks.ta_buildstats_title'),
                ),
            ],
            self::LastBackup => [
                new TaskActionData(
                    route: 'admin.backup',
                    icon: 'fas fa-cogs',
                    title: __('admin/tasks.ta_backup_title'),
                ),
            ],
            self::LastCleanup => [],
        };
    }
}
