<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;

class RepairController extends BaseController
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

        $this->buildPage();
    }

    private function buildPage(): void
    {
        if (!$_POST) {
            $tables = $this->getAllTables();

            $parse['display'] = 'block';
            $parse['head'] = Template::render('admin.repair_row_head');
            $parse['tables'] = '';
            $parse['results'] = '';

            foreach ($tables as $row) {
                $row['row'] = $row['TABLE_NAME'];
                $row['data'] = FormatLib::prettyBytes((int) $row['DATA_LENGTH']);
                $row['index'] = FormatLib::prettyBytes((int) $row['INDEX_LENGTH']);
                $row['overhead'] = FormatLib::prettyBytes((int) $row['DATA_FREE']);
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

                    DB::statement('CHECK TABLE ' . $table);
                    $parse['result'] = __('admin/repair.db_check_ok');
                    $result_rows .= Template::render('admin.repair_result', $parse);

                    if (isset($_POST['optimize']) && $_POST['optimize'] === 'on') {
                        DB::statement('OPTIMIZE TABLE ' . $table);
                        $parse['result'] = __('admin/repair.db_opt');
                        $result_rows .= Template::render('admin.repair_result', $parse);
                    }

                    if (isset($_POST['repair']) && $_POST['repair'] === 'on') {
                        DB::statement('REPAIR TABLE ' . $table);
                        $parse['result'] = __('admin/repair.db_rep');
                        $result_rows .= Template::render('admin.repair_result', $parse);
                    }
                }

                $parse['results'] = $result_rows;
            } else {
                Functions::redirect('admin/repair');
            }
        }

        Template::legacyView(
            'admin.repair',
            $parse
        );
    }

    private function getAllTables(): array
    {
        return array_map(
            fn($row) => (array) $row,
            DB::select(
                'SELECT TABLE_NAME, DATA_LENGTH, INDEX_LENGTH, DATA_FREE
                FROM information_schema.TABLES
                WHERE table_schema = ?',
                [config('DB_DATABASE')]
            )
        );
    }
}
