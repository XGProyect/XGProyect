<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xgp\App\Libraries\Buildings\Queue;
use Xgp\App\Libraries\Buildings\QueueElements;

/**
 * @covers Queue
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class QueueTest extends TestCase
{
    /**
     * @covers App\Libraries\Buildings\Queue::addElementToQueue
     */
    public function testAddOneElementToQueue(): void
    {
        $object = new Queue();
        $currentTime = time();

        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::addElementToQueue
     */
    public function testAddManyElementToQueue(): void
    {
        $object = new Queue();
        $currentTime = time();

        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build;2,5,90,' . $currentTime . ',destroy'
        );

        // add third element
        $queueElements = new QueueElements();
        $queueElements->building = 3;
        $queueElements->buildLevel = 10;
        $queueElements->buildTime = 120;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build;2,5,90,' . $currentTime . ',destroy;3,10,120,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::removeElementFromQueue
     */
    public function testRemoveElementFromQueueWithOneElement(): void
    {
        // add one element
        $object = new Queue();
        $currentTime = time();

        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);
        $object->removeElementFromQueue(0);

        $this->assertEquals(
            $object->returnQueueAsString(),
            ''
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::removeElementFromQueue
     */
    public function testRemoveLastElementFromQueueWithTwoElement(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        $object->removeElementFromQueue(1);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::removeElementFromQueue
     */
    public function testRemoveFirstElementFromQueueWithTwoElement(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        $object->removeElementFromQueue(0);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '2,5,90,' . $currentTime . ',destroy'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::removeElementFromQueue
     */
    public function testRemoveMiddleElementFromQueueWithThreeElement(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        // add third element
        $queueElements = new QueueElements();
        $queueElements->building = 3;
        $queueElements->buildLevel = 10;
        $queueElements->buildTime = 120;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        $object->removeElementFromQueue(1);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build;3,10,120,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::removeElementFromQueue
     */
    public function testRemoveElementFromQueueInvalidParameters(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        // add third element
        $queueElements = new QueueElements();
        $queueElements->building = 3;
        $queueElements->buildLevel = 10;
        $queueElements->buildTime = 120;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        $object->removeElementFromQueue(99999);

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build;2,5,90,' . $currentTime . ',destroy;3,10,120,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::getElementFromQueueAsArray
     */
    public function testGetElementFromQueueAsArray(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        // add third element
        $queueElements = new QueueElements();
        $queueElements->building = 3;
        $queueElements->buildLevel = 10;
        $queueElements->buildTime = 120;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        $this->assertEquals(
            $object->getElementFromQueueAsArray(1),
            [
                'building' => 2,
                'buildLevel' => 5,
                'buildTime' => 90,
                'buildEndTime' => $currentTime,
                'buildMode' => 'destroy',
            ]
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::returnQueueAsString
     */
    public function testReturnQueueAsString(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // Remove the following lines when you implement this test.
        $this->assertIsString(
            $object->returnQueueAsString()
        );

        $this->assertEquals(
            $object->returnQueueAsString(),
            '1,1,20,' . $currentTime . ',build'
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::returnQueueAsArray
     */
    public function testReturnQueueAsArray(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // Remove the following lines when you implement this test.
        $this->assertIsArray(
            $object->returnQueueAsArray()
        );

        $this->assertEquals(
            $object->returnQueueAsArray(),
            [
                0 =>
                [
                    'building' => 1,
                    'buildLevel' => 1,
                    'buildTime' => 20,
                    'buildEndTime' => $currentTime,
                    'buildMode' => 'build',
                ],
            ]
        );
    }

    /**
     * @covers App\Libraries\Buildings\Queue::countQueueElements
     */
    public function testCountQueueElements(): void
    {
        $object = new Queue();
        $currentTime = time();

        // add first element
        $queueElements = new QueueElements();
        $queueElements->building = 1;
        $queueElements->buildLevel = 1;
        $queueElements->buildTime = 20;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // add second element
        $queueElements = new QueueElements();
        $queueElements->building = 2;
        $queueElements->buildLevel = 5;
        $queueElements->buildTime = 90;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'destroy';

        $object->addElementToQueue($queueElements);

        // add third element
        $queueElements = new QueueElements();
        $queueElements->building = 3;
        $queueElements->buildLevel = 10;
        $queueElements->buildTime = 120;
        $queueElements->buildEndTime = $currentTime;
        $queueElements->buildMode = 'build';

        $object->addElementToQueue($queueElements);

        // Remove the following lines when you implement this test.
        $this->assertIsArray(
            $object->returnQueueAsArray()
        );

        $this->assertEquals(
            $object->countQueueElements(),
            3
        );
    }
}
