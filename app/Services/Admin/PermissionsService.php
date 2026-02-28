<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Adm\Permissions;

class PermissionsService
{
    private Permissions $permissions;

    public function __construct()
    {
        $this->permissions = new Permissions(
            Options::getInstance()->get('admin_permissions')
        );
    }

    public function buildViewData(): array
    {
        $sections_list = [];
        $modules_list = [];
        $permissions_list = [];

        $sections = $this->permissions->getAdminSections();
        $modules = $this->permissions->getAdminModules();
        $roles = $this->buildRolesList();

        foreach ($sections as $section_id => $section) {
            foreach ($modules[$section_id] as $module) {
                foreach ($roles as $role => $name) {
                    $permissions_list[] = [
                        'module' => $module,
                        'role' => $role,
                        'permission_checked' => $this->permissions->isAccessAllowed($module, $role) ? 'checked' : '',
                        'permission_disabled' => $role === UserRanks::ADMIN ? 'disabled' : '',
                    ];
                }

                $modules_list[] = [
                    'page_module' => $module,
                    'page_module_title' => __('admin/menu.' . $module),
                    'permissions_list' => $permissions_list,
                ];

                $permissions_list = [];
            }

            $sections_list[$section_id] = [
                'section_name' => ucfirst($section),
                'section_title' => __('admin/menu.' . $section),
                'roles_list' => $roles,
                'modules_list' => $modules_list,
            ];

            $modules_list = [];
        }

        return ['sections_list' => $sections_list];
    }

    public function updatePermissions(array $input): void
    {
        $modules = $this->permissions->getAdminModules();
        $roles = $this->permissions->getRoles(true);

        foreach ($modules as $module) {
            foreach ($module as $module_name) {
                foreach ($roles as $role) {
                    if (isset($input[$module_name][$role]) && $input[$module_name][$role] === 'on') {
                        $this->permissions->grantAccess($module_name, $role);
                    } else {
                        $this->permissions->removeAccess($module_name, $role);
                    }
                }
            }
        }

        $this->permissions->savePermissions();
    }

    private function buildRolesList(): array
    {
        $roles_list = [];

        foreach ($this->permissions->getRoles() as $role) {
            $roles_list[$role] = [
                'role_name' => __('admin/global.user_level')[$role],
            ];
        }

        return $roles_list;
    }
}
