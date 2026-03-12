<?php

declare(strict_types=1);

namespace App\Data\Admin;

readonly class TaskData
{
    /**
     * @param array<int, TaskActionData> $actions
     */
    public function __construct(
        public string $name,
        public string $nextRun,
        public string $lastRun,
        public array $actions,
    ) {
    }
}
