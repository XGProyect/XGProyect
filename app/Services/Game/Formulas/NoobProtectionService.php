<?php

declare(strict_types=1);

namespace App\Services\Game\Formulas;

use App\Services\SettingsService;

class NoobProtectionService
{
    private bool $protection;
    private int $protectionTime;
    private int $protectionMulti;
    private int $allowedLevel;

    public function __construct(SettingsService $settings)
    {
        $this->protection = $settings->getBool('noobprotection');
        $this->protectionTime = $settings->getInt('noobprotectiontime');
        $this->protectionMulti = max($settings->getInt('noobprotectionmulti'), 1);
        $this->allowedLevel = $settings->getInt('stat_admin_level');
    }

    public function isWeak(int $currentPoints, int $otherPoints): bool
    {
        if (!$this->protection) {
            return false;
        }

        if ($currentPoints > $otherPoints * $this->protectionMulti) {
            if ($otherPoints > $this->protectionTime && $this->protectionTime > 0) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function isStrong(int $currentPoints, int $otherPoints): bool
    {
        if (!$this->protection) {
            return false;
        }

        if ($currentPoints * $this->protectionMulti < $otherPoints) {
            if ($currentPoints > $this->protectionTime && $this->protectionTime > 0) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function isRankVisible(int $userAuthLevel): bool
    {
        return $userAuthLevel <= $this->allowedLevel;
    }
}
