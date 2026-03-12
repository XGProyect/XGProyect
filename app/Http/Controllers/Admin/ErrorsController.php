<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
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
            unlink($path);
        }

        return redirect()->route('admin.errors');
    }

    /**
     * @return array{errorsList: list<array{error_message: string, errors: list<string>, count: int}>, totalErrors: int}
     */
    private function parseErrorLog(): array
    {
        $path = $this->logFilePath();
        $grouped = [];
        $totalErrors = 0;

        if ($path !== null) {
            $contents = file_get_contents($path);

            if ($contents !== false) {
                // Split only on lines that start with a timestamp "[YYYY-MM-DD HH:MM:SS]"
                // Using a specific pattern avoids splitting on "[stacktrace]" lines
                $entries = preg_split('/\n(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', trim($contents));

                foreach ($entries ?: [] as $entry) {
                    $lines = array_values(array_filter(explode(PHP_EOL, $entry)));

                    if (empty($lines)) {
                        continue;
                    }

                    $header = array_shift($lines);

                    // Strip the timestamp prefix
                    $message = (string) preg_replace('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] /', '', $header);

                    // Drop the standalone "[stacktrace]" separator line Laravel adds
                    $lines = array_values(array_filter($lines, fn (string $l) => trim($l) !== '[stacktrace]'));
                    $key = md5($message);

                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'error_message' => $message,
                            'errors' => $lines,
                            'count' => 0,
                        ];
                    } else {
                        // Keep the most recent stack trace
                        $grouped[$key]['errors'] = $lines;
                    }

                    $grouped[$key]['count']++;
                    $totalErrors++;
                }
            }
        }

        usort($grouped, fn (array $a, array $b) => $b['count'] <=> $a['count']);

        return [
            'errorsList' => $grouped,
            'totalErrors' => $totalErrors,
        ];
    }

    private function logFilePath(): ?string
    {
        $path = storage_path('logs/xgproyect.log');

        return file_exists($path) ? $path : null;
    }
}
