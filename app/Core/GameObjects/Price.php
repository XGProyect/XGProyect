<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

class Price
{
    public function __construct(
        private int $metal = 0,
        private int $crystal = 0,
        private int $deuterium = 0,
        private int $energy = 0,
        private int $energyMax = 0,
        private float $factor = 1,
    ) {
    }

    public function getMetal(): int
    {
        return $this->metal;
    }

    public function getCrystal(): int
    {
        return $this->crystal;
    }

    public function getDeuterium(): int
    {
        return $this->deuterium;
    }

    public function getEnergy(): int
    {
        return $this->energy;
    }

    public function getEnergyMax(): int
    {
        return $this->energyMax;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    /**
     * @return array<string, int|float>
     */
    public function toArray(): array
    {
        $arr = [
            'metal' => $this->metal,
            'crystal' => $this->crystal,
            'deuterium' => $this->deuterium,
            'factor' => $this->factor,
        ];

        if ($this->energyMax > 0) {
            $arr['energy_max'] = $this->energyMax;
        } else {
            $arr['energy'] = $this->energy;
        }

        return $arr;
    }
}
