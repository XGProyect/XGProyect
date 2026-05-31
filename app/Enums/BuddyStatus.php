<?php

declare(strict_types=1);

namespace App\Enums;

enum BuddyStatus: int
{
    case Pending = 0;
    case Accepted = 1;
}
