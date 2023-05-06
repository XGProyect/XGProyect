<?php

namespace Xgp\App\Libraries\Adm;

use Exception;
use JsonException;
use Xgp\App\Core\Enumerators\AdminPagesEnumerator as AdminPages;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Helpers\ArraysHelper;
use Xgp\App\Libraries\Functions;

class Permissions
{
    private const ALLOW_ADMIN_MODIFICATION = false;
    private array $permissions = [];

    public function __construct(string $permissions)
    {
        try {
            if (is_array($permissions)) {
                throw new Exception('JSON Expected!');
            }

            $this->setPermissions($permissions);
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function getAllPermissionsAsArray(): array
    {
        return $this->permissions;
    }

    public function getAllPermissionsAsJsonString(): string
    {
        try {
            return json_encode($this->permissions, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    public function getRoles(bool $no_admin = false): array
    {
        $roles = [
            UserRanks::GO,
            UserRanks::SGO,
            UserRanks::ADMIN,
        ];

        if ($no_admin) {
            unset($roles[array_search(UserRanks::ADMIN, $roles)]);
        }

        return $roles;
    }

    public function getAdminModules(): array
    {
        return [
            AdminPages::CONFIGURATION,
            AdminPages::INFORMATION,
            AdminPages::EDITION,
            AdminPages::TOOLS,
            AdminPages::MAINTENANCE,
        ];
    }

    public function getAdminSections(): array
    {
        return AdminPages::SECTIONS;
    }

    public function savePermissions(): void
    {
        Functions::updateConfig('admin_permissions', $this->getAllPermissionsAsJsonString());
    }

    public function isAccessAllowed(string $module, int $role): bool
    {
        return ($role === UserRanks::ADMIN or (isset($this->permissions[$module][$role]) && $this->permissions[$module][$role] === 1));
    }

    public function grantAccess(string $module, int $role): void
    {
        if ($this->moduleExists($module) && $this->roleExists($role) && $this->isRoleEditable($role)) {
            $this->permissions[$module][$role] = 1;
        }
    }

    public function removeAccess(string $module, int $role): void
    {
        if ($this->moduleExists($module) && $this->roleExists($role) && $this->isRoleEditable($role)) {
            $this->permissions[$module][$role] = 0;
        }
    }

    public function moduleExists(string $module): bool
    {
        return ArraysHelper::inMultiArray($module, $this->getAdminModules());
    }

    public function roleExists(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    private function isRoleEditable(int $role): bool
    {
        if ($role == UserRanks::ADMIN) {
            return self::ALLOW_ADMIN_MODIFICATION;
        }

        return true;
    }

    private function setPermissions(string $permissions): void
    {
        try {
            $this->permissions = json_decode($permissions, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    private function getPermissions(): array
    {
        return $this->permissions;
    }
}
