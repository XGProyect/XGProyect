<?php

declare(strict_types=1);

namespace Xgp\App\Core\Enumerators;

abstract class UserRanksEnumerator
{
    public const PLAYER = 0;
    public const GO = 1;
    public const SGO = 2;
    public const ADMIN = 3;
}
