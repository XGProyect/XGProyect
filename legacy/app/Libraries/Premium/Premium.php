<?php

namespace Xgp\App\Libraries\Premium;

use Xgp\App\Core\Entity\PremiumEntity;

class Premium
{
    private array $premium = [];
    private int $current_user_id = 0;

    public function __construct($premium, $current_user_id)
    {
        if (is_array($premium)) {
            $this->setUp($premium);
            $this->setUserId($current_user_id);
        }
    }

    public function getPremium(): array
    {
        $list_of_premium = [];

        foreach ($this->premium as $premium) {
            if (($premium instanceof PremiumEntity)) {
                $list_of_premium[] = $premium;
            }
        }

        return $list_of_premium;
    }

    public function getCurrentPremium(): PremiumEntity
    {
        return $this->getPremium()[0];
    }

    private function setUp($premiums): void
    {
        foreach ($premiums as $premium) {
            $this->premium[] = $this->createNewPremiumEntity($premium);
        }
    }

    private function setUserId(int $userId): void
    {
        $this->current_user_id = $userId;
    }

    /**
     *
     * @return int
     */
    private function getUserId(): int
    {
        return $this->current_user_id;
    }

    private function createNewPremiumEntity(array $premium): PremiumEntity
    {
        return new PremiumEntity($premium);
    }
}
