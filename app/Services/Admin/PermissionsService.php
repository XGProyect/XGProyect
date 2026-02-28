<?php

declare(strict_types=1);

namespace App\Services\Admin;

use JsonException;
use RuntimeException;
use Xgp\App\Core\Enumerators\AdminPagesEnumerator as AdminPages;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;

class PermissionsService
{
    private const ALLOW_ADMIN_MODIFICATION = false;

    private array $permissions = [];

    public function __construct(private readonly Options $options)
    {
        try {
            $this->permissions = json_decode(
                $this->options->get('admin_permissions'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to load admin permissions: ' . $e->getMessage(), 0, $e);
        }
    }

    public function buildViewData(): array
    {
        $sectionsList = [];
        $modulesList = [];
        $permissionsList = [];

        $sections = $this->sections();
        $modules = $this->modules();
        $roles = $this->buildRolesList();

        foreach ($sections as $sectionId => $section) {
            foreach ($modules[$sectionId] as $module) {
                foreach ($roles as $role => $name) {
                    $permissionsList[] = [
                        'module' => $module,
                        'role' => $role,
                        'permission_checked' => $this->isAccessAllowed($module, $role) ? 'checked' : '',
                        'permission_disabled' => $role === UserRanks::ADMIN ? 'disabled' : '',
                    ];
                }

                $modulesList[] = [
                    'page_module' => $module,
                    'page_module_title' => __('admin/menu.' . $module),
                    'permissions_list' => $permissionsList,
                ];

                $permissionsList = [];
            }

            $sectionsList[$sectionId] = [
                'section_name' => ucfirst($section),
                'section_title' => __('admin/menu.' . $section),
                'roles_list' => $roles,
                'modules_list' => $modulesList,
            ];

            $modulesList = [];
        }

        return ['sections_list' => $sectionsList];
    }

    public function updatePermissions(array $input): void
    {
        $roles = $this->roles(excludeAdmin: true);

        foreach ($this->modules() as $section) {
            foreach ($section as $module) {
                foreach ($roles as $role) {
                    if (isset($input[$module][$role]) && $input[$module][$role] === 'on') {
                        $this->grantAccess($module, $role);
                    } else {
                        $this->removeAccess($module, $role);
                    }
                }
            }
        }

        $this->savePermissions();
    }

    private function savePermissions(): void
    {
        try {
            $this->options->write(
                'admin_permissions',
                json_encode($this->permissions, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to save admin permissions: ' . $e->getMessage(), 0, $e);
        }
    }

    private function isAccessAllowed(string $module, int $role): bool
    {
        return $role === UserRanks::ADMIN
            || (isset($this->permissions[$module][$role]) && $this->permissions[$module][$role] === 1);
    }

    private function grantAccess(string $module, int $role): void
    {
        if ($this->moduleExists($module) && $this->roleExists($role) && $this->isRoleEditable($role)) {
            $this->permissions[$module][$role] = 1;
        }
    }

    private function removeAccess(string $module, int $role): void
    {
        if ($this->moduleExists($module) && $this->roleExists($role) && $this->isRoleEditable($role)) {
            $this->permissions[$module][$role] = 0;
        }
    }

    private function moduleExists(string $module): bool
    {
        return in_array($module, array_merge(...array_values($this->modules())));
    }

    private function roleExists(int $role): bool
    {
        return in_array($role, $this->roles());
    }

    private function isRoleEditable(int $role): bool
    {
        return $role !== UserRanks::ADMIN || self::ALLOW_ADMIN_MODIFICATION;
    }

    private function roles(bool $excludeAdmin = false): array
    {
        $roles = [UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN];

        return $excludeAdmin
            ? array_values(array_filter($roles, fn(int $r) => $r !== UserRanks::ADMIN))
            : $roles;
    }

    private function modules(): array
    {
        return [
            AdminPages::CONFIGURATION,
            AdminPages::INFORMATION,
            AdminPages::EDITION,
            AdminPages::TOOLS,
            AdminPages::MAINTENANCE,
        ];
    }

    private function sections(): array
    {
        return AdminPages::SECTIONS;
    }

    private function buildRolesList(): array
    {
        $rolesList = [];

        foreach ($this->roles() as $role) {
            $rolesList[$role] = [
                'role_name' => __('admin/global.user_level')[$role],
            ];
        }

        return $rolesList;
    }
}
