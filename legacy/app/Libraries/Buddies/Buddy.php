<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Buddies;

use Xgp\App\Core\Entity\BuddyEntity;
use Xgp\App\Core\Enumerators\BuddiesStatusEnumerator as BuddiesStatus;

class Buddy
{
    private array $_buddies = [];
    private int $_current_user_id = 0;

    public function __construct(array $buddies, int $current_user_id)
    {
        $this->setUp($buddies);
        $this->setUserId($current_user_id);
    }

    /**
     * Get all the players that received a request from this user
     *
     * @return array
     */
    public function getSentRequests()
    {
        $list_of_buddies = [];

        foreach ($this->_buddies as $buddy) {
            if (($buddy instanceof BuddyEntity) &&
                !$this->isBuddy($buddy) &&
                $this->isOwnRequest($buddy)) {
                $list_of_buddies[] = $buddy;
            }
        }

        return $list_of_buddies;
    }

    /**
     * Get all the players that sent a request to this user
     *
     * @return array
     */
    public function getReceivedRequests()
    {
        $list_of_buddies = [];

        foreach ($this->_buddies as $buddy) {
            if (($buddy instanceof BuddyEntity) &&
                !$this->isBuddy($buddy) &&
                !$this->isOwnRequest($buddy)) {
                $list_of_buddies[] = $buddy;
            }
        }

        return $list_of_buddies;
    }

    /**
     * Get all the players that are the current user's buddies
     *
     * @return array
     */
    public function getBuddies()
    {
        $list_of_buddies = [];

        foreach ($this->_buddies as $buddy) {
            if (($buddy instanceof BuddyEntity) && $this->isBuddy($buddy)) {
                $list_of_buddies[] = $buddy;
            }
        }

        return $list_of_buddies;
    }

    /**
     * Check if is already a buddy
     *
     * @param BuddyEntity $buddy Buddy
     *
     * @return boolean
     */
    private function isBuddy(BuddyEntity $buddy)
    {
        return ($buddy->getBuddyStatus() == BuddiesStatus::isBuddy);
    }

    /**
     * Check if is the request owner
     *
     * @param BuddyEntity $buddy Buddy
     *
     * @return boolean
     */
    private function isOwnRequest(BuddyEntity $buddy)
    {
        return ($buddy->getBuddySender() == $this->getUserId());
    }

    /**
     * Set up the list of buddies
     *
     * @param array $buddies Buddies
     *
     * @return void
     */
    private function setUp($buddies)
    {
        foreach ($buddies as $buddy) {
            $this->_buddies[] = $this->createNewBuddyEntity($buddy);
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

    private function createNewBuddyEntity(array $buddy): BuddyEntity
    {
        return new BuddyEntity($buddy);
    }
}
