<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum BuildingId: int
{
    case MetalMine = 1;
    case CrystalMine = 2;
    case DeuteriumSynthesizer = 3;
    case SolarPlant = 4;
    case FusionReactor = 12;
    case RobotFactory = 14;
    case NanoFactory = 15;
    case Hangar = 21;
    case MetalStore = 22;
    case CrystalStore = 23;
    case DeuteriumTank = 24;
    case Laboratory = 31;
    case Terraformer = 33;
    case AllyDeposit = 34;
    case Mondbasis = 41;
    case Phalanx = 42;
    case JumpGate = 43;
    case MissileSilo = 44;
}
