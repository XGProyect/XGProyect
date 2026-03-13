<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum OfficerId: int
{
    case Commander = 601;
    case Admiral = 602;
    case Engineer = 603;
    case Geologist = 604;
    case Technocrat = 605;
}
