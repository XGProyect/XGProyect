<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ErrorsController extends BaseController
{
    public function index(): View
    {
        return view('admin.errors', $this->parseErrorLog());
    }

    public function export(): BinaryFileResponse | RedirectResponse
    {
        $path = $this->logFilePath();

        if ($path === null) {
            return redirect()->route('admin.errors');
        }

        return response()->download($path, 'xgproyect.log', ['Content-Type' => 'text/plain']);
    }

    public function deleteAll(): RedirectResponse
    {
        $path = $this->logFilePath();

        if ($path !== null) {
            File::delete($path);
        }

        return redirect()->route('admin.errors');
    }

    /**
     * @return array{errorsList: array<array{error_message: string, errors: list<string>, count: int}>, totalErrors: int}
     */
    private function parseErrorLog(): array
    {
        $path = $this->logFilePath();

        if ($path === null) {
            return ['errorsList' => [], 'totalErrors' => 0];
        }

        /** @var Collection<string, array{error_message: string, errors: list<string>, count: int}> $grouped */
        $grouped = collect(preg_split('/\n(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', trim(File::get($path))) ?: [])
            ->filter()
            ->map(function (string $entry): array {
                $lines = array_values(array_filter(explode(PHP_EOL, $entry)));
                $message = (string) preg_replace('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] /', '', array_shift($lines) ?? '');
                $trace = collect($lines)
                    ->reject(fn (string $l) => trim($l) === '[stacktrace]')
                    ->values()
                    ->all();

                return ['message' => $message, 'trace' => $trace];
            })
            ->groupBy(fn (array $e) => md5((string) $e['message']))
            ->map(fn (Collection $group): array => [
                'error_message' => (string) ($group->last()['message'] ?? ''),
                'errors' => (array) ($group->last()['trace'] ?? []),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return [
            'errorsList' => $grouped->all(),
            'totalErrors' => (int) $grouped->sum('count'),
        ];
    }

    private function logFilePath(): ?string
    {
        $path = storage_path('logs/xgproyect.log');

        return File::exists($path) ? $path : null;
    }
}
