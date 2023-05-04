<?php

namespace Xgp\App\Http\Controllers\Game;

class FacilitiesController extends BuildingsController
{
    protected string $page = 'facilities';
    protected array $allowedBuildings = [
        1 => [14, 15, 21, 31, 33, 34, 44],
        3 => [14, 21, 41, 42, 43],
    ];
}
