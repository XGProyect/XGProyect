<?php

declare(strict_types=1);

namespace Xgp\App\Core\Entity;

use Xgp\App\Core\Entity;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class ChangelogEntity extends Entity
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function getChangelogId(): int
    {
        return (int) $this->data['changelog_id'];
    }

    public function getChangelogLangId(): int
    {
        return (int) $this->data['changelog_lang_id'];
    }

    public function getChangelogVersion(): string
    {
        return (string) $this->data['changelog_version'];
    }

    public function getChangelogDate(): string
    {
        return (string) $this->data['changelog_date'];
    }

    public function getChangelogDescription(): string
    {
        return (string) $this->data['changelog_description'];
    }
}
