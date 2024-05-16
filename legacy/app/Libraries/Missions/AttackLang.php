<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Missions;

use Xgp\App\Libraries\BattleEngine\Utils\Lang;

class AttackLang implements Lang
{
    private $objects;

    public function __construct($objects)
    {
        $this->objects = $objects;
    }

    public function getShipName(int $id): string
    {
        return __('game/ships.' . $this->objects[$id]);
    }

    public function getAttackersAttackingDescr(int $amount, int $damage): string
    {
        return sprintf(__('game/combatreport.cr_fleet_attack_1'), $amount, $damage);
    }

    public function getDefendersDefendingDescr(int $damage): string
    {
        return sprintf(__('game/combatreport.cr_fleet_attack_2'), $damage);
    }

    public function getDefendersAttackingDescr(int $amount, int $damage): string
    {
        return sprintf(__('game/combatreport.cr_fleet_defs_1'), $amount, $damage);
    }

    public function getAttackersDefendingDescr(int $damage): string
    {
        return sprintf(__('game/combatreport.cr_fleet_defs_2'), $damage);
    }

    public function getTechs(int $weaponsTech, int $shieldsTech, int $armourTech): string
    {
        return sprintf(__('game/combatreport.cr_technologies'), ($weaponsTech * 10), ($shieldsTech * 10), ($armourTech * 10));
    }

    public function getAttackerHasWon(): string
    {
        return __('game/combatreport.cr_attacker_won');
    }

    public function getDefendersHasWon(): string
    {
        return __('game/combatreport.cr_defender_won');
    }

    public function getDraw(): string
    {
        return __('game/combatreport.cr_both_won');
    }

    public function getStoleDescr(int $metal, int $crystal, int $deuterium): string
    {
        return sprintf(__('game/combatreport.cr_stealed_ressources'), $metal, $crystal, $deuterium);
    }

    public function getAttackersLostUnits(int $units): string
    {
        return sprintf(__('game/combatreport.cr_attacker_lostunits'), $units);
    }

    public function getDefendersLostUnits(int $units): string
    {
        return sprintf(__('game/combatreport.cr_defender_lostunits'), $units);
    }

    public function getFloatingDebris(int $metal, int $crystal): string
    {
        return sprintf(__('game/combatreport.cr_debris_units'), $metal, $crystal);
    }

    public function getMoonProb(int $prob): string
    {
        return sprintf(__('game/combatreport.cr_moonproba'), $prob);
    }

    public function getNewMoon(string $name, int $galaxy, int $system, int $planet): string
    {
        return sprintf(__('game/combatreport.cr_moonbuilt'), $name, $galaxy, $system, $planet);
    }
}
