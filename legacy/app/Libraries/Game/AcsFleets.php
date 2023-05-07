<?php

namespace Xgp\App\Libraries\Game;

use Xgp\App\Core\Entity\AcsFleetEntity;

class AcsFleets
{
    private array $_acs = [];
    private int $_current_user_id = 0;

    public function __construct($acs, $current_user_id)
    {
        if (is_array($acs)) {
            $this->setUp($acs);
            $this->setUserId($current_user_id);
        }
    }

    public function getAcs(): array
    {
        $list_of_acs = [];

        foreach ($this->_acs as $acs) {
            if (($acs instanceof AcsFleetEntity)) {
                $list_of_acs[] = $acs;
            }
        }

        return $list_of_acs;
    }

    public function getFirstAcs(): AcsFleetEntity
    {
        return $this->getAcs()[0];
    }

    private function setUp(array $acsFleets): void
    {
        foreach ($acsFleets as $acs) {
            $data = $this->createNewAcsFleetEntity($acs);

            $this->_acs[] = $data;
        }
    }

    private function setUserId(int $userId): void
    {
        $this->_current_user_id = $userId;
    }

    private function getUserId(): int
    {
        return $this->_current_user_id;
    }

    private function createNewAcsFleetEntity(array $fleet): AcsFleetEntity
    {
        return new AcsFleetEntity($fleet);
    }
}
