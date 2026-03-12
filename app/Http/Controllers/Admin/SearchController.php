<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\Admin\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class SearchController extends BaseController
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function __invoke(Request $request): View
    {
        $query = $request->string('term')->trim()->toString();
        $results = strlen($query) >= 3
            ? $this->searchService->search($query)
            : collect();

        return view('admin.search', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
