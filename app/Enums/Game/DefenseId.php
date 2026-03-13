<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum DefenseId: int
{
    case RocketLauncher = 401;
    case LightLaser = 402;
    case HeavyLaser = 403;
    case GaussCannon = 404;
    case IonCannon = 405;
    case PlasmaTurret = 406;
    case SmallShieldDome = 407;
    case LargeShieldDome = 408;
    case AntiBallisticMissile = 502;
    case InterplanetaryMissile = 503;
}
