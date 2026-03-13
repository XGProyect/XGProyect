<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum DriveType: string
{
    case Combustion = 'combustion';
    case Impulse = 'impulse';
    case Hyperspace = 'hyperspace';
    case None = 'none';

    /**
     * Speed bonus multiplier per tech level for this drive type.
     */
    public function speedBonus(): float
    {
        return match ($this) {
            self::Combustion => 0.1,
            self::Impulse => 0.2,
            self::Hyperspace => 0.3,
            self::None => 0.0,
        };
    }

    /**
     * The research ID that governs this drive type.
     */
    public function researchId(): ?ResearchId
    {
        return match ($this) {
            self::Combustion => ResearchId::CombustionDrive,
            self::Impulse => ResearchId::ImpulseDrive,
            self::Hyperspace => ResearchId::HyperspaceDrive,
            self::None => null,
        };
    }
}
