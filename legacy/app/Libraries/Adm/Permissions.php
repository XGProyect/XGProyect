<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Adm;

use JsonException;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

class Permissions
{
    private array $permissions = [];

    public function __construct(string $permissions)
    {
        try {
            $this->permissions = json_decode($permissions, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    public function isAccessAllowed(string $module, int $role): bool
    {
        return ($role === UserRanks::ADMIN || (isset($this->permissions[$module][$role]) && $this->permissions[$module][$role] === 1));
    }
}
