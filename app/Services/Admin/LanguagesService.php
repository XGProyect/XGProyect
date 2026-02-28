<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class LanguagesService
{
    /**
     * Returns all .php files under lang/ as relative paths, sorted.
     *
     * @return string[]
     */
    public function getFiles(): array
    {
        return collect(File::allFiles(lang_path()))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => $file->getRelativePathname())
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Load and flatten a language file into an ordered list of key/value pairs.
     * Nested arrays are flattened using dot notation (e.g. "user_level.0").
     *
     * @return array<int, array{key: string, value: string}>
     */
    public function loadTranslations(string $relativePath): array
    {
        $path = $this->resolvePath($relativePath);

        try {
            $data = require $path;
        } catch (\ParseError) {
            return [];
        }

        if (!is_array($data)) {
            return [];
        }

        return array_values(
            array_map(
                fn (string $key, string $value) => ['key' => $key, 'value' => $value],
                array_keys($this->flatten($data)),
                array_values($this->flatten($data)),
            )
        );
    }

    /**
     * Reconstruct and overwrite a language file from an ordered list of key/value pairs.
     *
     * @param array<int, array{key: string, value: string}> $pairs
     */
    public function saveTranslations(string $relativePath, array $pairs): void
    {
        $path = $this->resolvePath($relativePath);

        $flat = [];
        foreach ($pairs as $pair) {
            $flat[$pair['key']] = $pair['value'] ?? '';
        }

        $data    = $this->unflatten($flat);
        $content = "<?php\n\nreturn " . $this->exportArray($data) . ";\n";

        File::put($path, $content);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve and validate a relative lang path, guarding against path traversal.
     */
    private function resolvePath(string $relativePath): string
    {
        $langDir  = realpath(lang_path());
        $resolved = realpath(lang_path($relativePath));

        if (!$resolved || !$langDir || !str_starts_with($resolved, $langDir . DIRECTORY_SEPARATOR)) {
            abort(403, 'Invalid language file path.');
        }

        return $resolved;
    }

    /**
     * Recursively flatten a nested array into dot-notation keys.
     *
     * @return array<string, string>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix !== '' ? "$prefix.$key" : (string) $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flatten($value, $fullKey));
            } else {
                $result[$fullKey] = (string) $value;
            }
        }

        return $result;
    }

    /**
     * Reconstruct a nested array from dot-notation keys.
     * Numeric segments (e.g. "user_level.0") are cast to integer keys.
     *
     * @param  array<string, string> $flat
     */
    private function unflatten(array $flat): array
    {
        $result = [];

        foreach ($flat as $dotKey => $value) {
            $segments = explode('.', (string) $dotKey);
            $this->setNested($result, $segments, $value);
        }

        return $result;
    }

    private function setNested(array &$arr, array $keys, string $value): void
    {
        $key = array_shift($keys);
        $key = is_numeric($key) ? (int) $key : $key;

        if (empty($keys)) {
            $arr[$key] = $value;
            return;
        }

        if (!isset($arr[$key]) || !is_array($arr[$key])) {
            $arr[$key] = [];
        }

        $this->setNested($arr[$key], $keys, $value);
    }

    /**
     * Produce clean, single-quoted PHP array syntax (bracket style).
     */
    private function exportArray(array $data, int $indent = 1): string
    {
        $pad        = str_repeat('    ', $indent);
        $closingPad = str_repeat('    ', $indent - 1);
        $lines      = [];

        foreach ($data as $key => $value) {
            $keyStr = is_int($key) ? (string) $key : "'" . $this->escapeString((string) $key) . "'";

            if (is_array($value)) {
                $lines[] = "$pad$keyStr => " . $this->exportArray($value, $indent + 1) . ',';
            } else {
                $escaped = $this->escapeString((string) $value);
                $lines[] = "$pad$keyStr => '$escaped',";
            }
        }

        return "[\n" . implode("\n", $lines) . "\n{$closingPad}]";
    }

    private function escapeString(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
