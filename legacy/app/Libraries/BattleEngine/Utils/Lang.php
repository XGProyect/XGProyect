<?php

namespace Xgp\App\Libraries\BattleEngine\Utils;

interface Lang
{
    public function getShipName(int $id): string;

    public function getAttackersAttackingDescr(int $amount, int $damage): string;

    public function getDefendersDefendingDescr(int $damage): string;

    public function getDefendersAttackingDescr(int $amount, int $damage): string;

    public function getAttackersDefendingDescr(int $damage): string;

    public function getTechs(int $weaponsTech, int $shieldsTech, int $armourTech): string;

    public function getAttackerHasWon(): string;

    public function getDefendersHasWon(): string;

    public function getDraw(): string;

    public function getStoleDescr(int $metal, int $crystal, int $deuterium): string;

    public function getAttackersLostUnits(int $units): string;

    public function getDefendersLostUnits(int $units): string;

    public function getFloatingDebris(int $metal, int $crystal): string;

    public function getMoonProb(int $prob): string;

    public function getNewMoon(string $name, int $galaxy, int $system, int $planet): string;
}
