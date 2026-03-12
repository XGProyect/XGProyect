<?php

declare(strict_types=1);

namespace App\Data\Admin;

readonly class TaskActionData
{
    public function __construct(
        public string $route,
        public string $icon,
        public string $title,
    ) {
    }
}
