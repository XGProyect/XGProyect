<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;

class ModulesController extends BaseController
{
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
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
            $modules_count = count(explode(';', Options::getInstance()->get('modules')));

            for ($i = 0; $i < $modules_count; $i++) {
                $modules_set[] = (isset($modules["status{$i}"]) ? 1 : 0);
            }

            Options::getInstance()->write('modules', join(';', $modules_set));

            session()->flash('success', __('admin/modules.mdl_all_ok_message'));
        }
    }

    private function buildModulesList(): array
    {
        $modules_list = [];

        $modules = explode(';', Options::getInstance()->get('modules'));

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
