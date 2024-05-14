<?php

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Repair;

class RepairController extends BaseController
{
    private Repair $repairModel;
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

        $this->repairModel = new Repair();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        if (!$_POST) {
            $tables = $this->repairModel->getAllTables();

            $parse['display'] = 'block';
            $parse['head'] = Template::render('admin.repair_row_head');
            $parse['tables'] = '';
            $parse['results'] = '';

            foreach ($tables as $row) {
                $row['row'] = $row['TABLE_NAME'];
                $row['data'] = FormatLib::prettyBytes($row['DATA_LENGTH']);
                $row['index'] = FormatLib::prettyBytes($row['INDEX_LENGTH']);
                $row['overhead'] = FormatLib::prettyBytes($row['DATA_FREE']);
                $row['status_style'] = 'text-info';

                $parse['tables'] .= Template::render(
                    'admin.repair_row',
                    $row
                );
            }
        } else {
            $parse['display'] = 'none';
            $parse['head'] = Template::render('admin.repair_result_head');
            $parse['tables'] = '';

            if (isset($_POST['table']) && is_array($_POST['table'])) {
                $result_rows = '';

                foreach ($_POST['table'] as $table) {
                    $parse['row'] = $table;

                    $this->repairModel->checkTable($table);
                    $parse['result'] = __('admin/repair.db_check_ok');
                    $result_rows .= Template::render('admin.repair_result', $parse);

                    if (isset($_POST['optimize']) && $_POST['optimize'] === 'on') {
                        $this->repairModel->optimizeTable($table);
                        $parse['result'] = __('admin/repair.db_opt');
                        $result_rows .= Template::render('admin.repair_result', $parse);
                    }

                    if (isset($_POST['repair']) && $_POST['repair'] === 'on') {
                        $this->repairModel->repairTable($table);
                        $parse['result'] = __('admin/repair.db_rep');
                        $result_rows .= Template::render('admin.repair_result', $parse);
                    }
                }

                $parse['results'] = $result_rows;
            } else {
                Functions::redirect('admin.php?page=repair');
            }
        }

        Template::legacyView(
            'admin.repair',
            $parse
        );
    }
}
