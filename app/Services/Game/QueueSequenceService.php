<?php

declare(strict_types=1);

namespace App\Services\Game;

use Illuminate\Support\Collection;

class QueueSequenceService
{
    /**
     * @param  Collection<int, object>  $queue
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

            $duration = (int) $durationResolver($item);

            $runningTime += $duration;
            $item->position = $index + 1;
            $item->duration = $duration;
            $item->end_time = $runningTime;
        }
    }

    /**
     * @param  Collection<int, object>  $queue
     */
    public function removeTailOccurrenceAtPosition(
        Collection $queue,
        int $position,
        callable $objectIdResolver,
    ): bool {
        $targetItem = $queue->firstWhere('position', $position);

        if (!is_object($targetItem)) {
            return false;
        }

        $lastOccurrence = $this->findLastOccurrenceForRemoval($queue, $targetItem, $objectIdResolver);

        if ($lastOccurrence === null) {
            return false;
        }

        $removedPosition = (int) $lastOccurrence->position;
        $removedDuration = (int) $lastOccurrence->duration;
        $lastOccurrence->delete();

        foreach ($queue->where('position', '>', $removedPosition)->sortBy('position') as $item) {
            $item->position -= 1;
            $item->end_time -= $removedDuration;
            $item->save();
        }

        return true;
    }

    /**
     * @param  Collection<int, object>  $queue
     */
    public function findLastOccurrenceForRemoval(
        Collection $queue,
        object $targetItem,
        callable $objectIdResolver,
    ): ?object {
        $targetObjectId = $objectIdResolver($targetItem);

        $candidate = $queue
            ->filter(
                static fn (object $item): bool => $objectIdResolver($item) === $targetObjectId &&
                    (int) $item->position >= (int) $targetItem->position
            )
            ->sortBy('position')
            ->last();

        return is_object($candidate) ? $candidate : null;
    }
}
