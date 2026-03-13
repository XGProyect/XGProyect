<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum BuildingCategory: string
{
    case Resource = 'resource';
    case Facility = 'facility';
    case Moon = 'moon';
}
