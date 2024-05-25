<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Research;

use Xgp\App\Core\Entity\ResearchEntity;

class Researches
{
    private array $_research = [];
    private int $_current_user_id = 0;

    public function __construct(array $research, int $current_user_id)
    {
        $this->setUp($research);
        $this->setUserId($current_user_id);
    }

    public function getResearch(): array
    {
        $list_of_research = [];

        foreach ($this->_research as $research) {
            if (($research instanceof ResearchEntity)) {
                $list_of_research[] = $research;
            }
        }

        return $list_of_research;
    }

    public function getCurrentResearch(): ResearchEntity
    {
        return $this->getResearch()[0];
    }

    private function setUp($researches): void
    {
        foreach ($researches as $research) {
            $this->_research[] = $this->createNewResearchEntity($research);
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

    private function createNewResearchEntity(array $research): ResearchEntity
    {
        return new ResearchEntity($research);
    }
}
