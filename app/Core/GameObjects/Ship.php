<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use Illuminate\Support\Collection;

class Ship extends GameObject
{
    /**
     * @param Collection<int, int> $requirements
     * @param Collection<int, int> $rapidFire
     *
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        int $id,
        string $name,
        Price $price,
        Collection $requirements,
        private float $shield,
        private float $attack,
        private Collection $rapidFire,
        private int $speed,
        private int $speed2,
        private int $consumption,
        private int $consumption2,
        private int $capacity,
        private DriveSpec $drive,
        private ?ProductionFormula $production = null,
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

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function getSpeed2(): int
    {
        return $this->speed2;
    }

    public function getConsumption(): int
    {
        return $this->consumption;
    }

    public function getConsumption2(): int
    {
        return $this->consumption2;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getDrive(): DriveSpec
    {
        return $this->drive;
    }

    public function getProduction(): ?ProductionFormula
    {
        return $this->production;
    }

    public function hasProduction(): bool
    {
        return $this->production !== null;
    }
}
