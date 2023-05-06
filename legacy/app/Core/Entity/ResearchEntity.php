<?php

namespace Xgp\App\Core\Entity;

use Xgp\App\Core\Entity;

class ResearchEntity extends Entity
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function getResearchId(): int
    {
        return (int) $this->data['research_id'];
    }

    public function getResearchUserId(): int
    {
        return (int) $this->data['research_user_id'];
    }

    public function getResearchCurrentResearch(): int
    {
        return (int) $this->data['research_current_research'];
    }

    public function getResearchEspionageTechnology(): int
    {
        return (int) $this->data['research_espionage_technology'];
    }

    public function getResearchComputerTechnology(): int
    {
        return (int) $this->data['research_computer_technology'];
    }

    public function getResearchWeaponsTechnology(): int
    {
        return (int) $this->data['research_weapons_technology'];
    }

    public function getResearchShieldingTechnology(): int
    {
        return (int) $this->data['research_shielding_technology'];
    }

    public function getResearchArmourTechnology(): int
    {
        return (int) $this->data['research_armour_technology'];
    }

    public function getResearchEnergyTechnology(): int
    {
        return (int) $this->data['research_energy_technology'];
    }

    public function getResearchHyperspaceTechnology(): int
    {
        return (int) $this->data['research_hyperspace_technology'];
    }

    public function getResearchCombustionDrive(): int
    {
        return (int) $this->data['research_combustion_drive'];
    }

    public function getResearchImpulseDrive(): int
    {
        return (int) $this->data['research_impulse_drive'];
    }

    public function getResearchHyperspaceDrive(): int
    {
        return (int) $this->data['research_hyperspace_drive'];
    }

    public function getResearchLaserTechnology(): int
    {
        return (int) $this->data['research_laser_technology'];
    }

    public function getResearchIonicTechnology(): int
    {
        return (int) $this->data['research_ionic_technology'];
    }

    public function getResearchPlasmaTechnology(): int
    {
        return (int) $this->data['research_plasma_technology'];
    }

    public function getResearchIntergalacticResearchNetwork(): int
    {
        return (int) $this->data['research_intergalactic_research_network'];
    }

    public function getResearchAstrophysics(): int
    {
        return (int) $this->data['research_astrophysics'];
    }

    public function getResearchGravitonTechnology(): int
    {
        return (int) $this->data['research_graviton_technology'];
    }
}
