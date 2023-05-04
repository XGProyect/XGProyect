<?php

namespace Xgp\App\Http\Controllers\Game;

class SuppliesController extends BuildingsController
{
    protected string $page = 'supplies';
    protected array $allowedBuildings = [
        1 => [1, 2, 3, 4, 12, 22, 23, 24],
        3 => [12, 22, 23, 24],
    ];
}
