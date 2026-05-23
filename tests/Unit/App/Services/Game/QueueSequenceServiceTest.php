<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

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

        $this->assertSame(1, $queue[0]->position);
        $this->assertSame(3, $queue[0]->targetLevel);
        $this->assertSame(30, $queue[0]->duration);
        $this->assertSame(130, $queue[0]->end_time);

        $this->assertSame(2, $queue[1]->position);
        $this->assertSame(4, $queue[1]->targetLevel);
        $this->assertSame(40, $queue[1]->duration);
        $this->assertSame(170, $queue[1]->end_time);

        $this->assertSame(3, $queue[2]->position);
        $this->assertSame(2, $queue[2]->targetLevel);
        $this->assertSame(20, $queue[2]->duration);
        $this->assertSame(190, $queue[2]->end_time);
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

        $result = $service->findLastOccurrenceForRemoval(
            $queue,
            $queue[0],
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

        $this->assertTrue($result);
        $this->assertTrue($queue[2]->deleted);
        $this->assertSame(4, $queue[3]->position);
        $this->assertSame(200, $queue[3]->end_time);
        $this->assertSame(1, $queue[3]->saveCalls);
    }
}

final class QueueItemDouble
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
}
