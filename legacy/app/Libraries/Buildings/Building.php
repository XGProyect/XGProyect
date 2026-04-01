<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Buildings;

use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\OfficerService;
use Xgp\App\Core\Enumerators\BuildingsEnumerator as Buildings;

class Building
{
    private $queue = '';
    private $planet = '';
    private $user = '';
    private $objects = '';
    private $building = 0;
    private $buildLevel = 0;
    private $buildTime = 0;
    private OfficerService $officerService;

    public function __construct($planet, $user, $objects, ?OfficerService $officerService = null)
    {
        $this->queue = new Queue($planet['planet_b_building_id']);
        $this->planet = $planet;
        $this->user = $user;
        $this->objects = $objects;
        $this->officerService = $officerService ?? app(OfficerService::class);
    }

    public function addBuilding($building_id): void
    {
        $this->building = $building_id;

        $this->queueElementToBuild();
    }

    public function removeBuilding(int $elementId): void
    {
        $this->removeElementFromBuildingQueue($elementId);
    }

    public function cancelBuilding(): void
    {
        $this->removeFirstElementFromBuildingQueue();
    }

    public function tearDownBuilding(int $buildingId): void
    {
        $this->building = $buildingId;

        $this->queueElementToTearDown();
    }

    public function getCountElementsOnQueue(): int
    {
        return $this->queue->countQueueElements();
    }

    public function getNewQueueAsString(): string
    {
        return $this->queue->returnQueueAsString();
    }

    public function getNewQueueAsArray(): array
    {
        return $this->queue->returnQueueAsArray();
    }

    public function isQueueFull(): bool
    {
        $queue_size = 1;

        if ($this->officerService->isOfficerActive((int) $this->user['premium_officier_commander'], time())) {
            $queue_size = MAX_BUILDING_QUEUE_SIZE;
        }

        return !($this->getCountElementsOnQueue() < $queue_size);
    }

    private function buildQueueElementsBlock(string $buildMode): ?QueueElements
    {
        $buildLevel = $this->calculateBuildLevel($buildMode);

        if ($buildLevel < 0) {
            return null;
        }

        $queueElements = new QueueElements();
        $queueElements->building = $this->building;
        $queueElements->buildLevel = $buildLevel;
        $queueElements->buildTime = $this->calculateBuildTime($buildMode);
        $queueElements->buildEndTime = $this->calculateBuildEndTime();
        $queueElements->buildMode = $buildMode;

        return $queueElements;
    }

    private function queueElementToBuild(): void
    {
        $this->queue->addElementToQueue(
            $this->buildQueueElementsBlock('build')
        );
    }

    private function queueElementToTearDown(): string
    {
        $this->queue->addElementToQueue(
            $this->buildQueueElementsBlock('teardown')
        );

        return $this->queue->returnQueueAsString();
    }

    private function removeElementFromBuildingQueue($elementId): void
    {
        $this->queue->removeElementFromQueue($elementId);
    }

    private function removeFirstElementFromBuildingQueue(): void
    {
        $this->removeElementFromBuildingQueue(0);
    }

    private function getBuildingCurrentLevel(): int
    {
        return $this->planet[$this->objects->getObjects($this->building)];
    }

    private function calculateBuildLevel(string $buildMode): int
    {
        $difference = ($buildMode == 'teardown') ? -1 : 1;

        return $this->getBuildingCurrentLevel() + $difference;
    }

    private function calculateBuildTime(string $buildMode): int
    {
        $difference = ($buildMode == 'teardown') ? 2 : 1;

        $this->buildTime = app(DevelopmentsService::class)->developmentTime(
            (int) $this->building,
            (int) $this->planet[$this->objects->getObjects($this->building)],
            (int) $this->planet[$this->objects->getObjects(Buildings::BUILDING_ROBOT_FACTORY)],
            (int) $this->planet[$this->objects->getObjects(Buildings::BUILDING_NANO_FACTORY)],
            0,
            0,
            false
        ) / $difference;

        return $this->buildTime;
    }

    /**
     * Calculate the building time for each element
     * depending if it's the first element in the queue
     * or there's something before.
     */
    private function calculateBuildEndTime(): int
    {
        if ($this->getCountElementsOnQueue() <= 0) {
            return time() + $this->buildTime;
        } else {
            $prev_element = $this->getCountElementsOnQueue() - 1;
            $prev_element_time = $this->queue->getElementFromQueueAsArray($prev_element)[2];

            return $prev_element_time + $this->buildTime;
        }
    }
}
