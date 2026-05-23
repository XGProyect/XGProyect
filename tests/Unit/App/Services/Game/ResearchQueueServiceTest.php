<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

use App\Models\ResearchQueue;
use App\Services\Game\QueueSequenceService;
use App\Services\Game\ResearchQueueService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[CoversClass(ResearchQueueService::class)]
class ResearchQueueServiceTest extends TestCase
{
    public function testRebuildQueueAfterHeadRemovalRenormalizesRepeatedTechnologyLevels(): void
    {
        $service = $this->buildService();
        $queue = new Collection([
            new ResearchQueue(['position' => 2, 'tech_id' => 108, 'target_level' => 4, 'duration' => 80, 'end_time' => 180]),
            new ResearchQueue(['position' => 3, 'tech_id' => 108, 'target_level' => 5, 'duration' => 90, 'end_time' => 270]),
            new ResearchQueue(['position' => 4, 'tech_id' => 109, 'target_level' => 2, 'duration' => 50, 'end_time' => 320]),
        ]);

        $this->callRebuildQueueAfterHeadRemoval(
            $service,
            $queue,
            removedTechId: 108,
            startTime: 100,
            durationResolver: static fn (ResearchQueue $item): int => $item->target_level * 10,
        );

        $this->assertSame(1, $queue[0]->position);
        $this->assertSame(3, $queue[0]->target_level);
        $this->assertSame(30, $queue[0]->duration);
        $this->assertSame(130, $queue[0]->end_time);

        $this->assertSame(2, $queue[1]->position);
        $this->assertSame(4, $queue[1]->target_level);
        $this->assertSame(40, $queue[1]->duration);
        $this->assertSame(170, $queue[1]->end_time);

        $this->assertSame(3, $queue[2]->position);
        $this->assertSame(2, $queue[2]->target_level);
        $this->assertSame(20, $queue[2]->duration);
        $this->assertSame(190, $queue[2]->end_time);
    }

    public function testFindLastQueuedOccurrenceForRemovalReturnsTailOfSelectedTechnology(): void
    {
        $service = $this->buildService();
        $queue = new Collection([
            new ResearchQueue(['position' => 2, 'tech_id' => 108, 'target_level' => 4]),
            new ResearchQueue(['position' => 3, 'tech_id' => 109, 'target_level' => 2]),
            new ResearchQueue(['position' => 4, 'tech_id' => 108, 'target_level' => 5]),
            new ResearchQueue(['position' => 5, 'tech_id' => 108, 'target_level' => 6]),
        ]);

        $result = $this->callFindLastQueuedOccurrenceForRemoval($service, $queue, $queue[0]);

        $this->assertInstanceOf(ResearchQueue::class, $result);
        $this->assertSame(5, $result->position);
        $this->assertSame(6, $result->target_level);
    }

    private function buildService(): ResearchQueueService
    {
        return new ResearchQueueService(
            registry: $this->createStub(\App\Core\GameObjects\GameObjectRegistry::class),
            queueSequenceService: new QueueSequenceService(),
            developmentDataService: $this->createStub(\App\Services\Game\DevelopmentDataService::class),
            developmentsService: $this->createStub(\App\Services\Game\Formulas\DevelopmentsService::class),
        );
    }

    /**
     * @param  Collection<int, ResearchQueue>  $queue
     * @param  callable(ResearchQueue): int  $durationResolver
     */
    private function callRebuildQueueAfterHeadRemoval(
        ResearchQueueService $service,
        Collection $queue,
        int $removedTechId,
        int $startTime,
        callable $durationResolver,
    ): void {
        $method = new ReflectionMethod(ResearchQueueService::class, 'rebuildQueueAfterHeadRemoval');
        $method->invoke($service, $queue, $removedTechId, $startTime, $durationResolver);
    }

    /**
     * @param  Collection<int, ResearchQueue>  $queue
     */
    private function callFindLastQueuedOccurrenceForRemoval(
        ResearchQueueService $service,
        Collection $queue,
        ResearchQueue $targetItem,
    ): ?ResearchQueue {
        $method = new ReflectionMethod(ResearchQueueService::class, 'findLastQueuedOccurrenceForRemoval');
        $result = $method->invoke($service, $queue, $targetItem);

        return $result instanceof ResearchQueue ? $result : null;
    }
}
