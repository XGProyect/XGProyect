<?php

namespace Xgp\App\Libraries\Alliance;

use Xgp\App\Core\Entity\AllianceEntity;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator;

class Alliances
{
    private array $_alliances = [];
    private int $_current_user_id = 0;
    private int $_current_user_rank_id = 0;

    public function __construct($alliances, $current_user_id, $current_user_rank_id = 0)
    {
        if (is_array($alliances)) {
            $alliances = array_filter($alliances);

            $this->setUp($alliances);
            $this->setUserId($current_user_id);
            $this->setUserRankId($current_user_rank_id);
        }
    }

    public function getAlliances(): array
    {
        $list_of_alliances = [];

        foreach ($this->_alliances as $alliance) {
            if (($alliance instanceof AllianceEntity)) {
                $list_of_alliances[] = $alliance;
            }
        }

        return $list_of_alliances;
    }

    public function getCurrentAlliance(): AllianceEntity
    {
        return $this->_alliances[0];
    }

    public function getCurrentAllianceRankObject(): Ranks
    {
        return new Ranks($this->getCurrentAlliance()->getAllianceRanks());
    }

    public function isOwner(): bool
    {
        return ($this->getCurrentAlliance()->getAllianceOwner() === $this->getUserId());
    }

    public function checkRank(int $rank): bool
    {
        $ranks = $this->getCurrentAllianceRankObject();

        return ($rank != null
            && $ranks->getAllRanksAsArray() != null
            && $ranks->getRankById($this->getUserRankId())['rights'][$rank] == SwitchIntEnumerator::on);
    }

    public function hasAccess(int $rank): bool
    {
        return ($this->isOwner() or $this->checkRank($rank));
    }

    private function setUp($alliances): void
    {
        foreach ($alliances as $alliance) {
            $this->_alliances[] = $this->createNewAllianceEntity($alliance);
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

    private function setUserRankId(int $user_rank_id): void
    {
        $this->_current_user_rank_id = $user_rank_id;
    }

    private function getUserRankId(): int
    {
        return $this->_current_user_rank_id;
    }

    private function createNewAllianceEntity(array $alliance): AllianceEntity
    {
        return new AllianceEntity($alliance);
    }
}
