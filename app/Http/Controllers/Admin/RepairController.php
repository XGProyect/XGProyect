<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RepairRequest;
use App\Services\AdministrationService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Libraries\FormatLib;

class RepairController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $tables = $this->getAllTables()->map(fn (array $row) => [
            'name' => $row['TABLE_NAME'],
            'data' => FormatLib::prettyBytes((int) $row['DATA_LENGTH']),
            'index' => FormatLib::prettyBytes((int) $row['INDEX_LENGTH']),
            'overhead' => FormatLib::prettyBytes((int) $row['DATA_FREE']),
        ]);

        return view('admin.repair', [
            'tables' => $tables,
            'results' => null,
        ]);
    }

    public function run(RepairRequest $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $validated = $request->validated();
        $selected  = $validated['table'];
        $optimize  = isset($validated['optimize']);
        $repair    = isset($validated['repair']);

        $results = collect();

        foreach ($selected as $table) {
            DB::statement('CHECK TABLE ' . $table);
            $results->push(['table' => $table, 'result' => __('admin/repair.db_check_ok')]);

            if ($optimize) {
                DB::statement('OPTIMIZE TABLE ' . $table);
                $results->push(['table' => $table, 'result' => __('admin/repair.db_opt')]);
            }

            if ($repair) {
                DB::statement('REPAIR TABLE ' . $table);
                $results->push(['table' => $table, 'result' => __('admin/repair.db_rep')]);
            }
        }

        return view('admin.repair', [
            'tables' => collect(),
            'results' => $results,
        ]);
    }

    private function getAllTables(): Collection
    {
        return collect(DB::select(
            'SELECT TABLE_NAME, DATA_LENGTH, INDEX_LENGTH, DATA_FREE
            FROM information_schema.TABLES
            WHERE table_schema = ?',
            [config('DB_DATABASE')]
        ))->map(fn (object $row) => (array) $row);
    }
}
