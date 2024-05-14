<?php

namespace Xgp\App\Core\Entity;

use Xgp\App\Core\Entity;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class AllianceEntity extends Entity
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getAllianceId(): int
    {
        return (int) $this->data['alliance_id'];
    }

    public function getAllianceName(): string
    {
        return (string) $this->data['alliance_name'];
    }

    public function getAllianceTag(): string
    {
        return (string) $this->data['alliance_tag'];
    }

    public function getAllianceOwner(): int
    {
        return (int) $this->data['alliance_owner'];
    }

    public function getAllianceRegisterTime(): int
    {
        return (int) $this->data['alliance_register_time'];
    }

    public function getAllianceDescription(): string
    {
        return (string) $this->data['alliance_description'];
    }

    public function getAllianceWeb(): string
    {
        return (string) $this->data['alliance_web'];
    }

    public function getAllianceText(): string
    {
        return (string) $this->data['alliance_text'];
    }

    public function getAllianceImage(): string
    {
        return (string) $this->data['alliance_image'];
    }

    public function getAllianceRequest(): string
    {
        return (string) $this->data['alliance_request'];
    }

    public function getAllianceRequestNotAllow(): int
    {
        return (int) $this->data['alliance_request_notallow'];
    }

    public function getAllianceRanks(): string
    {
        return (string) $this->data['alliance_ranks'];
    }

    /**
     * Return the alliance members
     *
     * @return string
     */
    public function getAllianceMembers()
    {
        return $this->data['alliance_members'];
    }
}
