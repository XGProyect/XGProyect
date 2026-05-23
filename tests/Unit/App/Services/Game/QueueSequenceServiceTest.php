<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

use App\Services\Game\QueueSequenceItem;
use App\Services\Game\QueueSequenceService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueueSequenceService::class)]
class QueueSequenceServiceTest extends TestCase
{
    public function testRebuildAfterHeadRemovalRenormalizesMatchingItems(): void
    {
        $service = new QueueSequenceService();
        $queue = new Collection([
            new QueueItemDouble(position: 2, objectId: 108, targetLevel: 4, duration: 80, endTime: 180),
            new QueueItemDouble(position: 3, objectId: 108, targetLevel: 5, duration: 90, endTime: 270),
            new QueueItemDouble(position: 4, objectId: 109, targetLevel: 2, duration: 50, endTime: 320),
        ]);

        $service->rebuildAfterHeadRemoval(
            $queue,
            108,
            100,
            static fn (QueueItemDouble $item): int => $item->objectId,
            static function (QueueItemDouble $item): void {
                $item->targetLevel -= 1;
            },
            static fn (QueueItemDouble $item): int => $item->targetLevel * 10,
        );

        $first = $queue->get(0);
        $second = $queue->get(1);
        $third = $queue->get(2);

        $this->assertInstanceOf(QueueItemDouble::class, $first);
        $this->assertInstanceOf(QueueItemDouble::class, $second);
        $this->assertInstanceOf(QueueItemDouble::class, $third);

        $this->assertSame(1, $first->position);
        $this->assertSame(3, $first->targetLevel);
        $this->assertSame(30, $first->duration);
        $this->assertSame(130, $first->end_time);

        $this->assertSame(2, $second->position);
        $this->assertSame(4, $second->targetLevel);
        $this->assertSame(40, $second->duration);
        $this->assertSame(170, $second->end_time);

        $this->assertSame(3, $third->position);
        $this->assertSame(2, $third->targetLevel);
        $this->assertSame(20, $third->duration);
        $this->assertSame(190, $third->end_time);
    }

    public function testFindLastOccurrenceForRemovalReturnsTailMatch(): void
    {
        $service = new QueueSequenceService();
        $queue = new Collection([
            new QueueItemDouble(position: 2, objectId: 108, targetLevel: 4, duration: 20, endTime: 120),
            new QueueItemDouble(position: 3, objectId: 109, targetLevel: 2, duration: 20, endTime: 140),
            new QueueItemDouble(position: 4, objectId: 108, targetLevel: 5, duration: 20, endTime: 160),
            new QueueItemDouble(position: 5, objectId: 108, targetLevel: 6, duration: 20, endTime: 180),
        ]);

        $first = $queue->get(0);

        $this->assertInstanceOf(QueueItemDouble::class, $first);

        $result = $service->findLastOccurrenceForRemoval(
            $queue,
            $first,
            static fn (QueueItemDouble $item): int => $item->objectId,
        );

        $this->assertInstanceOf(QueueItemDouble::class, $result);
        $this->assertSame(5, $result->position);
        $this->assertSame(6, $result->targetLevel);
    }

    public function testRemoveTailOccurrenceAtPositionDeletesLastMatchAndShiftsLaterItems(): void
    {
        $service = new QueueSequenceService();
        $queue = new Collection([
            new QueueItemDouble(position: 2, objectId: 108, targetLevel: 4, duration: 20, endTime: 120),
            new QueueItemDouble(position: 3, objectId: 109, targetLevel: 2, duration: 30, endTime: 150),
            new QueueItemDouble(position: 4, objectId: 108, targetLevel: 5, duration: 40, endTime: 190),
            new QueueItemDouble(position: 5, objectId: 110, targetLevel: 1, duration: 50, endTime: 240),
        ]);

        $result = $service->removeTailOccurrenceAtPosition(
            $queue,
            2,
            static fn (QueueItemDouble $item): int => $item->objectId,
        );

        $removed = $queue->get(2);
        $shifted = $queue->get(3);

        $this->assertInstanceOf(QueueItemDouble::class, $removed);
        $this->assertInstanceOf(QueueItemDouble::class, $shifted);
        $this->assertTrue($result);
        $this->assertTrue($removed->deleted);
        $this->assertSame(4, $shifted->position);
        $this->assertSame(200, $shifted->end_time);
        $this->assertSame(1, $shifted->saveCalls);
    }
}

final class QueueItemDouble implements QueueSequenceItem
{
    public bool $deleted = false;

    public int $saveCalls = 0;

    public int $position;

    public int $objectId;

    public int $targetLevel;

    public int $duration;

    public int $end_time;

    public function __construct(
        int $position,
        int $objectId,
        int $targetLevel,
        int $duration,
        int $endTime,
    ) {
        $this->position = $position;
        $this->objectId = $objectId;
        $this->targetLevel = $targetLevel;
        $this->duration = $duration;
        $this->end_time = $endTime;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function save(): void
    {
        $this->saveCalls++;
    }

    public function getQueuePosition(): int
    {
        return $this->position;
    }

    public function setQueuePosition(int $position): void
    {
        $this->position = $position;
    }

    public function getQueueDuration(): int
    {
        return $this->duration;
    }

    public function setQueueDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getQueueEndTime(): int
    {
        return $this->end_time;
    }

    public function setQueueEndTime(int $endTime): void
    {
        $this->end_time = $endTime;
    }

    public function removeFromQueue(): void
    {
        $this->delete();
    }

    public function persistQueueChanges(): void
    {
        $this->save();
    }
}
