<?php

namespace Xgp\App\Libraries;

use Xgp\App\Models\Libraries\MissionControlLib as MissionControlLibModel;

class MissionControlLib
{
    private ?MissionControlLibModel $missionControlLibModel = null;

    public function __construct()
    {
        $this->missionControlLibModel = new MissionControlLibModel();
    }

    public function arrivingFleets(): void
    {
        $this->processMissions(
            $this->missionControlLibModel->getArrivingFleets()
        );
    }

    public function returningFleets(): void
    {
        $this->processMissions(
            $this->missionControlLibModel->getReturningFleets()
        );
    }

    private function processMissions(array $allFleets = []): void
    {
        // validate
        if (!is_array($allFleets) or empty($allFleets)) {
            return;
        }

        // missions list
        $missions = [
            1 => 'Attack',
            2 => 'Acs',
            3 => 'Transport',
            4 => 'Deploy',
            5 => 'Stay',
            6 => 'Spy',
            7 => 'Colonize',
            8 => 'Recycle',
            9 => 'Destroy',
            10 => 'Missile',
            15 => 'Expedition',
        ];

        // Process missions
        foreach ($allFleets as $fleet) {
            $name = $missions[$fleet['fleet_mission']];
            $mission_name = $name . 'Mission';
            $class_name = 'Xgp\App\Libraries\Missions\\' . $name;

            $mission = new $class_name();
            $mission->$mission_name($fleet);
        }
    }
}
