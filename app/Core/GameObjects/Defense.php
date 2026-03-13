<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use Illuminate\Support\Collection;

class Defense extends GameObject
{
    /**
     * @param Collection<int, int> $requirements
     * @param Collection<int, int> $rapidFire
     */
    public function __construct(
        int $id,
        string $name,
        Price $price,
        Collection $requirements,
        private float $shield,
        private float $attack,
        private Collection $rapidFire,
    ) {
        parent::__construct($id, $name, $price, $requirements);
    }

    public function getShield(): float
    {
        return $this->shield;
    }

    public function getAttack(): float
    {
        return $this->attack;
    }

    /**
     * @return Collection<int, int>
     */
    public function getRapidFire(): Collection
    {
        return $this->rapidFire;
    }
}
