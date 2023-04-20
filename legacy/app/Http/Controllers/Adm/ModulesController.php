<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;

class ModulesController extends BaseController
{
    private string $alert = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage(__('adm/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction(): void
    {
        $modules = filter_input_array(INPUT_POST);

        if ($modules) {
            $modules_count = count(explode(';', Functions::readConfig('modules')));

            for ($i = 0; $i < $modules_count; $i++) {
                $modules_set[] = (isset($modules["status{$i}"]) ? 1 : 0);
            }

            Functions::updateConfig('modules', join(';', $modules_set));

            $this->alert = Administration::saveMessage('ok', $this->langs->line('mdl_all_ok_message'));
        }
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->set(
                'adm/modules_view',
                [
                    'alert' => $this->alert ?? '',
                    'modules' => $this->buildModulesList(),
                ]
            )
        );
    }

    /**
     * Build the list of modules
     *
     * @return array
     */
    private function buildModulesList(): array
    {
        $modules_list = [];

        $modules = explode(';', Functions::readConfig('modules'));

        if ($modules) {
            foreach ($modules as $module => $status) {
                if ($status != null) {
                    $modules_list[] = [
                        'module' => $module,
                        'module_name' => $this->langs->language['mdl_modules'][$module],
                        'module_value' => ($status == 1) ? 'checked' : '',
                        'color' => ($status == 1) ? 'success' : 'danger',
                    ];
                }
            }
        }

        return $modules_list;
    }
}
