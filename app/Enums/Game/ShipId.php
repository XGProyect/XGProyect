<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum ShipId: int
{
    case SmallCargo = 202;
    case LargeCargo = 203;
    case LightFighter = 204;
    case HeavyFighter = 205;
    case Cruiser = 206;
    case Battleship = 207;
    case ColonyShip = 208;
    case Recycler = 209;
    case EspionageProbe = 210;
    case Bomber = 211;
    case SolarSatellite = 212;
    case Destroyer = 213;
    case Deathstar = 214;
    case Battlecruiser = 215;
}
