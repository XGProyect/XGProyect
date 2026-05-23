<?php

declare(strict_types=1);

namespace App\Services\Game;

use Illuminate\Support\Collection;

class QueueSequenceService
{
    /**
     * @template T of QueueSequenceItem
     *
     * @param  Collection<int, T>  $queue
     * @param  callable(T): int  $objectIdResolver
     * @param  callable(T): void  $matchingItemNormalizer
     * @param  callable(T): int  $durationResolver
     */
    public function rebuildAfterHeadRemoval(
        Collection $queue,
        int $removedObjectId,
        int $startTime,
        callable $objectIdResolver,
        callable $matchingItemNormalizer,
        callable $durationResolver,
    ): void {
        $runningTime = $startTime;

        foreach ($queue->values() as $index => $item) {
            if ($objectIdResolver($item) === $removedObjectId) {
                $matchingItemNormalizer($item);
            }

            $duration = $durationResolver($item);

            $runningTime += $duration;
            $item->setQueuePosition($index + 1);
            $item->setQueueDuration($duration);
            $item->setQueueEndTime($runningTime);
        }
    }

    /**
     * @template T of QueueSequenceItem
     *
     * @param  Collection<int, T>  $queue
     * @param  callable(T): int  $objectIdResolver
     */
    public function removeTailOccurrenceAtPosition(
        Collection $queue,
        int $position,
        callable $objectIdResolver,
    ): bool {
        $targetItem = null;

        foreach ($queue as $item) {
            if ($item->getQueuePosition() === $position) {
                $targetItem = $item;

                break;
            }
        }

        if ($targetItem === null) {
            return false;
        }

        $lastOccurrence = $this->findLastOccurrenceForRemoval($queue, $targetItem, $objectIdResolver);

        if ($lastOccurrence === null) {
            return false;
        }

        $removedPosition = $lastOccurrence->getQueuePosition();
        $removedDuration = $lastOccurrence->getQueueDuration();
        $lastOccurrence->removeFromQueue();

        foreach ($queue as $item) {
            if ($item->getQueuePosition() <= $removedPosition) {
                continue;
            }

            $item->setQueuePosition($item->getQueuePosition() - 1);
            $item->setQueueEndTime($item->getQueueEndTime() - $removedDuration);
            $item->persistQueueChanges();
        }

        return true;
    }

    /**
     * @template T of QueueSequenceItem
     *
     * @param  Collection<int, T>  $queue
     * @param  T  $targetItem
     * @param  callable(T): int  $objectIdResolver
     *
     * @return T|null
     */
    public function findLastOccurrenceForRemoval(
        Collection $queue,
        QueueSequenceItem $targetItem,
        callable $objectIdResolver,
    ): ?QueueSequenceItem {
        $targetObjectId = $objectIdResolver($targetItem);
        $candidate = null;

        foreach ($queue as $item) {
            if ($objectIdResolver($item) !== $targetObjectId) {
                continue;
            }

            if ($item->getQueuePosition() < $targetItem->getQueuePosition()) {
                continue;
            }

            if ($candidate === null || $item->getQueuePosition() > $candidate->getQueuePosition()) {
                $candidate = $item;
            }
        }

        return $candidate;
    }
}
