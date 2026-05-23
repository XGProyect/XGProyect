<?php

declare(strict_types=1);

namespace App\Services\Game;

interface QueueSequenceItem
{
    public function getQueuePosition(): int;

    public function setQueuePosition(int $position): void;

    public function getQueueDuration(): int;

    public function setQueueDuration(int $duration): void;

    public function getQueueEndTime(): int;

    public function setQueueEndTime(int $endTime): void;

    public function removeFromQueue(): void;

    public function persistQueueChanges(): void;
}
