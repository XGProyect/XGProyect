<?php

declare(strict_types=1);

namespace App\Enums;

enum Module: int
{
    case Changelog = 0;
    case Overview = 1;
    case Empire = 2;
    case Buildings = 3;
    case ResourceSettings = 4;
    case Trader = 5;
    case Research = 6;
    case Shipyard = 7;
    case Fleet = 8;
    case FleetMovements = 9;
    case Technology = 10;
    case Galaxy = 11;
    case Defense = 12;
    case Alliance = 13;
    case Forum = 14;
    case Officers = 15;
    case Statistics = 16;
    case Search = 17;
    case Messages = 18;
    case Notes = 19;
    case Buddies = 20;
    case Options = 21;
    case Banned = 22;
    case CombatReports = 23;
    case Information = 24;
}
