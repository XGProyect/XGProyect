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

        $first = $queue->get(0);
        $second = $queue->get(1);
        $third = $queue->get(2);

        $this->assertInstanceOf(ResearchQueue::class, $first);
        $this->assertInstanceOf(ResearchQueue::class, $second);
        $this->assertInstanceOf(ResearchQueue::class, $third);

        $this->assertSame(1, $first->position);
        $this->assertSame(3, $first->target_level);
        $this->assertSame(30, $first->duration);
        $this->assertSame(130, $first->end_time);

        $this->assertSame(2, $second->position);
        $this->assertSame(4, $second->target_level);
        $this->assertSame(40, $second->duration);
        $this->assertSame(170, $second->end_time);

        $this->assertSame(3, $third->position);
        $this->assertSame(2, $third->target_level);
        $this->assertSame(20, $third->duration);
        $this->assertSame(190, $third->end_time);
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
}
