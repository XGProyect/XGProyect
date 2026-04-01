<?php

declare(strict_types=1);

namespace Xgp\App\Core\Entity;

use Xgp\App\Core\Entity;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class PremiumEntity extends Entity
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function getPremiumUserId(): int
    {
        return (int) $this->data['premium_user_id'];
    }

    public function getPremiumDarkMatter(): int
    {
        return (int) $this->data['premium_dark_matter'];
    }

    public function getPremiumOfficierCommander(): int
    {
        return (int) $this->data['premium_officier_commander'];
    }

    public function getPremiumOfficierAdmiral(): int
    {
        return (int) $this->data['premium_officier_admiral'];
    }

    public function getPremiumOfficierEngineer(): int
    {
        return (int) $this->data['premium_officier_engineer'];
    }

    public function getPremiumOfficierGeologist(): int
    {
        return (int) $this->data['premium_officier_geologist'];
    }

    public function getPremiumOfficierTechnocrat(): int
    {
        return (int) $this->data['premium_officier_technocrat'];
    }
}
