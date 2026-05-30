<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use Closure;

class ProductionFormula
{
    /**
     * @param int $baseMetal Base metal cost for production calculation
     * @param int $baseCrystal Base crystal cost for production calculation
     * @param int $baseDeuterium Base deuterium cost for production calculation
     * @param float $factor Growth factor per level
     * @param Closure(int $level, float $levelFactor): float $metalFormula
     * @param Closure(int $level, float $levelFactor): float $crystalFormula
     * @param Closure(int $level, float $levelFactor, float $planetTemp): float $deuteriumFormula
     * @param Closure(int $level, float $levelFactor, float $planetTemp, int $energyTech): float $energyFormula
     */
    public function __construct(
        private int $baseMetal,
        private int $baseCrystal,
        private int $baseDeuterium,
        private float $factor,
        private Closure $metalFormula,
        private Closure $crystalFormula,
        private Closure $deuteriumFormula,
        private Closure $energyFormula,
    ) {
    }

    public function getBaseMetal(): int
    {
        return $this->baseMetal;
    }

    public function getBaseCrystal(): int
    {
        return $this->baseCrystal;
    }

    public function getBaseDeuterium(): int
    {
        return $this->baseDeuterium;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function calculateMetal(int $level, float $levelFactor): float
    {
        return ($this->metalFormula)($level, $levelFactor);
    }

    public function calculateCrystal(int $level, float $levelFactor): float
    {
        return ($this->crystalFormula)($level, $levelFactor);
    }

    public function calculateDeuterium(int $level, float $levelFactor, float $planetTemp = 0): float
    {
        return ($this->deuteriumFormula)($level, $levelFactor, $planetTemp);
    }

    public function calculateEnergy(int $level, float $levelFactor, float $planetTemp = 0, int $energyTech = 0): float
    {
        return ($this->energyFormula)($level, $levelFactor, $planetTemp, $energyTech);
    }

    /**
     * Get the legacy-compatible array format for backward compatibility.
     *
     * @return array{metal: int, crystal: int, deuterium: int, energy: int, factor: float}
     */
    public function toLegacyArray(): array
    {
        return [
            'metal' => $this->baseMetal,
            'crystal' => $this->baseCrystal,
            'deuterium' => $this->baseDeuterium,
            'energy' => 0,
            'factor' => $this->factor,
        ];
    }
}
