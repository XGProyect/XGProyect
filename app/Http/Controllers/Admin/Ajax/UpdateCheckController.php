<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ajax;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Throwable;

class UpdateCheckController extends BaseController
{
    public function __invoke(): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get('https://updates.xgproyect.org/latest.json');

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (Throwable) {
            // silently fail
        }

        return response()->json([]);
    }
}
