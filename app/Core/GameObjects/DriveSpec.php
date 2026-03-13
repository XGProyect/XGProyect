<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use App\Enums\Game\DriveType;

class DriveSpec
{
    public function __construct(
        private DriveType $primary,
        private ?DriveType $secondary = null,
        private ?int $secondaryMinLevel = null,
        private ?DriveType $tertiary = null,
        private ?int $tertiaryMinLevel = null,
    ) {
    }

    public function getPrimary(): DriveType
    {
        return $this->primary;
    }

    public function getSecondary(): ?DriveType
    {
        return $this->secondary;
    }

    public function getSecondaryMinLevel(): ?int
    {
        return $this->secondaryMinLevel;
    }

    public function getTertiary(): ?DriveType
    {
        return $this->tertiary;
    }

    public function getTertiaryMinLevel(): ?int
    {
        return $this->tertiaryMinLevel;
    }

    /**
     * Determine the active drive type given the player's relevant tech levels.
     *
     * @param int $secondaryTechLevel Level of the research tied to the secondary drive
     * @param int $tertiaryTechLevel  Level of the research tied to the tertiary drive
     */
    public function getActiveDrive(int $secondaryTechLevel = 0, int $tertiaryTechLevel = 0): DriveType
    {
        if (
            $this->tertiary !== null &&
            $this->tertiaryMinLevel !== null &&
            $tertiaryTechLevel >= $this->tertiaryMinLevel
        ) {
            return $this->tertiary;
        }

        if (
            $this->secondary !== null &&
            $this->secondaryMinLevel !== null &&
            $secondaryTechLevel >= $this->secondaryMinLevel
        ) {
            return $this->secondary;
        }

        return $this->primary;
    }

    /**
     * Whether the ship uses `speed2` (upgraded speed) for the given tech levels.
     */
    public function usesUpgradedSpeed(int $secondaryTechLevel = 0, int $tertiaryTechLevel = 0): bool
    {
        return $this->getActiveDrive($secondaryTechLevel, $tertiaryTechLevel) !== $this->primary;
    }
}
