<?php

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Repair;

class RepairController extends BaseController
{
    private Repair $repairModel;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        $this->repairModel = new Repair();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        $parse['alert'] = '';

        if (!$_POST) {
            $tables = $this->repairModel->getAllTables();

            $parse['display'] = 'block';
            $parse['head'] = Template::getInstance()->render('admin.repair_row_head_view');
            $parse['tables'] = '';
            $parse['results'] = '';

            foreach ($tables as $row) {
                $row['row'] = $row['TABLE_NAME'];
                $row['data'] = FormatLib::prettyBytes($row['DATA_LENGTH']);
                $row['index'] = FormatLib::prettyBytes($row['INDEX_LENGTH']);
                $row['overhead'] = FormatLib::prettyBytes($row['DATA_FREE']);
                $row['status_style'] = 'text-info';

                $parse['tables'] .= Template::getInstance()->render(
                    'admin.repair_row_view',
                    $row
                );
            }
        } else {
            $parse['display'] = 'none';
            $parse['head'] = Template::getInstance()->render('admin.repair_result_head_view');
            $parse['tables'] = '';

            if (isset($_POST['table']) && is_array($_POST['table'])) {
                $result_rows = '';

                foreach ($_POST['table'] as $key => $table) {
                    $parse['row'] = $table;

                    $this->repairModel->checkTable($table);
                    $parse['result'] = __('admin/repair.db_check_ok');
                    $result_rows .= Template::getInstance()->render('admin.repair_result_view', $parse);

                    if (isset($_POST['Optimize']) && $_POST['Optimize'] == 'yes') {
                        $this->repairModel->optimizeTable($table);
                        $parse['result'] = __('admin/repair.db_opt');
                        $result_rows .= Template::getInstance()->render('admin.repair_result_view', $parse);
                    }

                    if (isset($_POST['Repair']) && $_POST['Repair'] == 'yes') {
                        $this->repairModel->repairTable($table);
                        $parse['result'] = __('admin/repair.db_rep');
                        $result_rows .= Template::getInstance()->render('admin.repair_result_view', $parse);
                    }
                }

                $parse['results'] = $result_rows;
            } else {
                Functions::redirect('admin.php?page=repair');
            }
        }

        Template::getInstance()->view(
            'admin.repair_view',
            $parse
        );
    }
}
