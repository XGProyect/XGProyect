<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Buildings;

final class Queue
{
    public const QUEUE_SEPARATOR = ';';
    public const ITEM_SEPARATOR = ',';

    private array | string $queue = [];

    public function __construct(array | string $current_queue = [])
    {
        $this->queue = $current_queue;
    }

    /**
     * Process the queue and put it into an array format
     */
    private function breakDownCurrentQueue(): void
    {
        // extract elements and filter empty values
        $elements = array_filter(explode(self::QUEUE_SEPARATOR, $this->queue));
        $queue = [];

        if (is_array($elements)) {
            foreach ($elements as $element_id => $content) {
                $queue[$element_id] = explode(self::ITEM_SEPARATOR, $content);
            }
        }

        $this->queue = $queue;
    }

    /**
     * Process the queue and put into a string
     */
    private function makeUpCurrentQueue(): void
    {
        if (isset($this->queue)) {
            $queue = $this->queue;

            foreach ($queue as $element_id => $content) {
                $queue[$element_id] = join(self::ITEM_SEPARATOR, $content);
            }

            $this->queue = join(self::QUEUE_SEPARATOR, $queue);
        }
    }

    /**
     * Adds an element to the queue
     */
    public function addElementToQueue(QueueElements $queue_elements): void
    {
        if (is_object($queue_elements)) {
            if (!is_array($this->queue)) {
                $this->breakDownCurrentQueue();
            }

            // convert the object to an array and put it to the end
            array_push($this->queue, (array) $queue_elements);
        }
    }

    /**
     * Removes an element from the queue
     */
    public function removeElementFromQueue(int $element_id): void
    {
        if (is_int($element_id)) {
            if (!is_array($this->queue)) {
                $this->breakDownCurrentQueue();
            }

            // unset that element from the array
            unset($this->queue[$element_id]);
        }
    }

    /**
     * Returns an element from the queue
     */
    public function getElementFromQueueAsArray(int $element_id): array
    {
        if (isset($this->queue)) {
            if (!is_array($this->queue)) {
                $this->breakDownCurrentQueue();
            }

            return $this->queue[$element_id];
        }

        return [];
    }

    /**
     * Returns the queue as a string
     */
    public function returnQueueAsString(): string
    {
        if (isset($this->queue)) {
            if (is_array($this->queue)) {
                $this->makeUpCurrentQueue();
            }

            return $this->queue;
        }

        return '';
    }

    /**
     * Returns the queue as an associative array
     */
    public function returnQueueAsArray(): array
    {
        if (isset($this->queue)) {
            if (!is_array($this->queue)) {
                $this->breakDownCurrentQueue();
            }

            return $this->queue;
        }

        return [];
    }

    /**
     * Count the amount of elements of the current queue
     */
    public function countQueueElements(): int
    {
        if (isset($this->queue)) {
            if (!is_array($this->queue)) {
                $this->breakDownCurrentQueue();
            }

            return count($this->queue);
        }

        return 0;
    }
}
