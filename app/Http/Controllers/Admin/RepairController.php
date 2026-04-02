<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RepairRequest;
use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairController extends BaseController
{
    public function __construct(private FormatService $formatService)
    {
    }

    public function index(): View
    {
        $tables = $this->getAllTables()->map(function (array $row) {
            /** @var array{TABLE_NAME: string, DATA_LENGTH: int, INDEX_LENGTH: int, DATA_FREE: int} $row */
            return [
                'name' => $row['TABLE_NAME'],
                'data' => $this->formatService->prettyBytes($row['DATA_LENGTH']),
                'index' => $this->formatService->prettyBytes($row['INDEX_LENGTH']),
                'overhead' => $this->formatService->prettyBytes($row['DATA_FREE']),
            ];
        });

        return view('admin.repair', [
            'tables' => $tables,
            'results' => null,
        ]);
    }

    public function run(RepairRequest $request): View
    {
        $validated = $request->validated();
        /** @var array<int, string> $selected */
        $selected = (array) $validated['table'];
        $optimize = isset($validated['optimize']);
        $repair = isset($validated['repair']);

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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getAllTables(): Collection
    {
        /** @var Collection<int, array<string, mixed>> $result */
        $result = collect(DB::select(
            'SELECT TABLE_NAME, DATA_LENGTH, INDEX_LENGTH, DATA_FREE
            FROM information_schema.TABLES
            WHERE table_schema = ?',
            [config('DB_DATABASE')]
        ))->map(fn (object $row) => (array) $row);

        return $result;
    }
}
