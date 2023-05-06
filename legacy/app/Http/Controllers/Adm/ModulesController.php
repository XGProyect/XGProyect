<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class ModulesController extends BaseController
{
    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->runAction();

        Template::getInstance()->view(
            'admin.modules',
            [
                'modules' => $this->buildModulesList(),
            ]
        );
    }

    private function runAction(): void
    {
        $modules = request()->all();

        if (count($modules) > 1) {
            $modules_count = count(explode(';', Functions::readConfig('modules')));

            for ($i = 0; $i < $modules_count; $i++) {
                $modules_set[] = (isset($modules["status{$i}"]) ? 1 : 0);
            }

            Functions::updateConfig('modules', join(';', $modules_set));

            session()->flash('success', __('admin/modules.mdl_all_ok_message'));
        }
    }

    private function buildModulesList(): array
    {
        $modules_list = [];

        $modules = explode(';', Functions::readConfig('modules'));

        if ($modules) {
            foreach ($modules as $module => $status) {
                if ($status != null) {
                    $modules_list[] = [
                        'module' => $module,
                        'module_name' => __('admin/modules.mdl_modules')[$module],
                        'module_value' => ($status == 1) ? 'checked' : '',
                        'color' => ($status == 1) ? 'success' : 'danger',
                    ];
                }
            }
        }

        return $modules_list;
    }
}
