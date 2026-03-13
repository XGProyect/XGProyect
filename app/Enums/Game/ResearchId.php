<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum ResearchId: int
{
    case EspionageTechnology = 106;
    case ComputerTechnology = 108;
    case WeaponsTechnology = 109;
    case ShieldingTechnology = 110;
    case ArmourTechnology = 111;
    case EnergyTechnology = 113;
    case HyperspaceTechnology = 114;
    case CombustionDrive = 115;
    case ImpulseDrive = 117;
    case HyperspaceDrive = 118;
    case LaserTechnology = 120;
    case IonicTechnology = 121;
    case PlasmaTechnology = 122;
    case IntergalacticResearchNetwork = 123;
    case Astrophysics = 124;
    case GravitonTechnology = 199;
}
