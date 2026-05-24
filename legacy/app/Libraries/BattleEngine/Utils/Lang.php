<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\BattleEngine\Utils;

interface Lang
{
    public function getShipName(int $id): string;

    public function getAttackersAttackingDescr(string | int $amount, string | int $damage): string;

    public function getDefendersDefendingDescr(string | int $damage): string;

    public function getDefendersAttackingDescr(string | int $amount, string | int $damage): string;

    public function getAttackersDefendingDescr(string | int $damage): string;

    public function getTechs(int $weaponsTech, int $shieldsTech, int $armourTech): string;

    public function getAttackerHasWon(): string;

    public function getDefendersHasWon(): string;

    public function getDraw(): string;

    public function getStoleDescr(string | int $metal, string | int $crystal, string | int $deuterium): string;

    public function getAttackersLostUnits(string | int $units): string;

    public function getDefendersLostUnits(string | int $units): string;

    public function getFloatingDebris(string | int $metal, string | int $crystal): string;

    public function getMoonProb(int $prob): string;

    public function getNewMoon(string $name, int $galaxy, int $system, int $planet): string;
}
