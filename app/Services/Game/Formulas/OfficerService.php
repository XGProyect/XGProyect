<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

class OfficerService
{
    public function isOfficerActive(int $expireTime, int $currentTime): bool
    {
        return $expireTime > $currentTime;
    }

    public function getMaxEspionage(int $espionageTech, bool $technocrateActive): int
    {
        return $espionageTech + ($technocrateActive ? TECHNOCRATE_SPY : 0);
    }

    public function getMaxComputer(int $computerTech, bool $admiralActive): int
    {
        return 1 + $computerTech + ($admiralActive ? AMIRAL : 0);
    }

    public function getDaysLeft(int $expireTime, int $currentTime): float
    {
        return ($expireTime - $currentTime) / 86400;
    }
}
