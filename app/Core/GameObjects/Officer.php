<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

class Officer
{
    public function __construct(
        private int $id,
        private string $name,
        private int $darkmatterWeek,
        private int $darkmatterMonth,
        private string $imgBig,
        private string $imgSmall,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDarkmatterWeek(): int
    {
        return $this->darkmatterWeek;
    }

    public function getDarkmatterMonth(): int
    {
        return $this->darkmatterMonth;
    }

    public function getImgBig(): string
    {
        return $this->imgBig;
    }

    public function getImgSmall(): string
    {
        return $this->imgSmall;
    }
}
