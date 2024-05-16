<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Xgp\App\Core\Enumerators\DefensesEnumerator as Defenses;

class DefensesController extends ShipyardController
{
    protected string $page = 'defenses';
    protected string $langFile = 'defenses';
    protected array $allowedStructures = [
        Defenses::defense_rocket_launcher,
        Defenses::defense_light_laser,
        Defenses::defense_heavy_laser,
        Defenses::defense_gauss_cannon,
        Defenses::defense_ion_cannon,
        Defenses::defense_plasma_turret,
        Defenses::defense_small_shield_dome,
        Defenses::defense_large_shield_dome,
        Defenses::defense_anti_ballistic_missile,
        Defenses::defense_interplanetary_missile,
    ];
    protected array $missiles = [
        Defenses::defense_anti_ballistic_missile => 0,
        Defenses::defense_interplanetary_missile => 0,
    ];
}
