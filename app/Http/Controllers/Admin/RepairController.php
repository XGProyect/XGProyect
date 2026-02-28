<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Libraries\FormatLib;

class RepairController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {}

    public static function make(): static
    {
        return new static(new AdministrationService(new SettingsService()));
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return $request->isMethod('post')
            ? $this->handlePost($request)
            : $this->showTables();
    }

    private function showTables(): View
    {
        $tables = $this->getAllTables()->map(fn (array $row) => [
            'name'     => $row['TABLE_NAME'],
            'data'     => FormatLib::prettyBytes((int) $row['DATA_LENGTH']),
            'index'    => FormatLib::prettyBytes((int) $row['INDEX_LENGTH']),
            'overhead' => FormatLib::prettyBytes((int) $row['DATA_FREE']),
        ]);

        return view('admin.repair', [
            'tables'  => $tables,
            'results' => null,
        ]);
    }

    private function handlePost(Request $request): View|RedirectResponse
    {
        $selected = $request->input('table', []);

        if (empty($selected) || ! is_array($selected)) {
            return redirect('admin/repair');
        }

        $results = collect();

        foreach ($selected as $table) {
            DB::statement('CHECK TABLE ' . $table);
            $results->push(['table' => $table, 'result' => __('admin/repair.db_check_ok')]);

            if ($request->boolean('optimize')) {
                DB::statement('OPTIMIZE TABLE ' . $table);
                $results->push(['table' => $table, 'result' => __('admin/repair.db_opt')]);
            }

            if ($request->boolean('repair')) {
                DB::statement('REPAIR TABLE ' . $table);
                $results->push(['table' => $table, 'result' => __('admin/repair.db_rep')]);
            }
        }

        return view('admin.repair', [
            'tables'  => collect(),
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
