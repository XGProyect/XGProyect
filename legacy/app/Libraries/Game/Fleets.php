<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Game;

use Xgp\App\Core\Entity\FleetEntity;
use Xgp\App\Core\Enumerators\MissionsEnumerator as Missions;

class Fleets
{
    private array $_fleets = [];
    private int $_current_user_id = 0;
    private int $_fleet_count = 0;
    private int $_expedition_count = 0;
    private array $_fleets_index = [];

    public function __construct($fleets, $current_user_id)
    {
        if (is_array($fleets)) {
            $this->setUp($fleets);
            $this->setUserId($current_user_id);
        }
    }

    public function getFleets(): array
    {
        $list_of_fleets = [];

        foreach ($this->_fleets as $fleets) {
            if (($fleets instanceof FleetEntity)) {
                $list_of_fleets[] = $fleets;
            }
        }

        return $list_of_fleets;
    }

    public function getFleetById(int $fleet_id): FleetEntity
    {
        return $this->_fleets[$this->validateIndex($fleet_id)] ?? new FleetEntity([]);
    }

    public function getOwnFleetById(int $fleet_id): ?FleetEntity
    {
        $fleet = $this->getFleetById($fleet_id);

        if ($fleet->getFleetOwner() == $this->getUserId()) {
            return $fleet;
        }

        return null;
    }

    public function getOwnValidFleetById(int $fleet_id): ?FleetEntity
    {
        $fleet = $this->getOwnFleetById($fleet_id);

        if ($fleet->getFleetStartTime() <= time()
            or $fleet->getFleetEndTime() < time()
            or $fleet->getFleetMess() == 1) {
            return null;
        }

        return $fleet;
    }

    private function validateIndex(int $fleet_id): int
    {
        return isset($this->_fleets_index[$fleet_id]) ? $this->_fleets_index[$fleet_id] : -1;
    }

    private function setUp(array $fleets): void
    {
        $index = 0;

        foreach ($fleets as $fleet) {
            $data = $this->createNewFleetEntity($fleet);

            $this->_fleets[] = $data;
            $this->_fleets_index[$data->getFleetId()] = $index++;

            $this->setFleetsCount();

            if ($data->getFleetMission() == Missions::EXPEDITION) {
                $this->setExpeditionsCount();
            }
        }
    }

    private function setUserId(int $userId): void
    {
        $this->_current_user_id = $userId;
    }

    private function setFleetsCount(): void
    {
        ++$this->_fleet_count;
    }

    private function setExpeditionsCount(): void
    {
        ++$this->_expedition_count;
    }

    private function getUserId(): int
    {
        return $this->_current_user_id;
    }

    public function getFleetsCount(): int
    {
        return $this->_fleet_count;
    }

    public function getExpeditionsCount(): int
    {
        return $this->_expedition_count;
    }

    private function createNewFleetEntity(array $fleet): FleetEntity
    {
        return new FleetEntity($fleet);
    }
}
