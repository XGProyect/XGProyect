<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class LanguagesService
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    /**
     * Returns language files grouped by subfolder, then by filename, then by locale.
     * The outer key is the group path (e.g. "admin", "home/ajax") or "" for root-level files.
     * The middle key is the filename (e.g. "alliances.php").
     * The inner key is the locale (e.g. "en"), the value is the relative path for the file selector.
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public function getGroupedFiles(): array
    {
        /** @var array<string, array<string, array<string, string>>> $grouped */
        $grouped = collect($this->files->allFiles(lang_path()))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()))
            ->sort()
            ->values()
            ->reduce(function (array $carry, string $path): array {
                $parts = explode('/', $path);
                $locale = $parts[0];
                $filename = basename($path);
                $group = implode('/', array_slice($parts, 1, -1));
                $carry[$group][$filename][$locale] = $path;

                return $carry;
            }, []);

        ksort($grouped);

        return $grouped;
    }

    /**
     * Load and flatten a language file into an ordered list of key/value pairs.
     * Nested arrays are flattened using dot notation (e.g. "user_level.0").
     *
     * Returns null when the file cannot be parsed or does not contain a PHP array
     * (i.e. a real error). Returns an empty array for valid files that have no entries.
     *
     * @return list<array{key: string, value: string}>|null
     */
    public function loadTranslations(string $relativePath): ?array
    {
        $path = $this->resolvePath($relativePath);

        try {
            $data = require $path;
        } catch (\ParseError) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        return collect($this->flatten($data))
            ->map(fn (string $value, string $key) => ['key' => $key, 'value' => $value])
            ->values()
            ->all();
    }

    /**
     * Reconstruct and overwrite a language file from an ordered list of key/value pairs.
     *
     * @param list<array{key: string, value: string}> $pairs
     */
    public function saveTranslations(string $relativePath, array $pairs): void
    {
        $path = $this->resolvePath($relativePath);

        $flat = collect($pairs)
            ->mapWithKeys(fn (array $pair) => [$pair['key'] => $pair['value']])
            ->all();

        $data = $this->unflatten($flat);
        $content = "<?php\n\nreturn " . $this->exportArray($data) . ";\n";

        $this->files->put($path, $content);
    }

    /**
     * Resolve and validate a relative lang path, guarding against path traversal.
     */
    private function resolvePath(string $relativePath): string
    {
        $langDir = realpath(lang_path());
        $resolved = realpath(lang_path($relativePath));

        if (!$resolved || !$langDir || !str_starts_with($resolved, $langDir . DIRECTORY_SEPARATOR)) {
            abort(403, 'Invalid language file path.');
        }

        return $resolved;
    }

    /**
     * Recursively flatten a nested array into dot-notation keys.
     *
     * @param  array<string|int, mixed> $data
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
                continue;
            }

            $result[$fullKey] = is_scalar($value) ? (string) $value : '';
        }

        return $result;
    }

    /**
     * Reconstruct a nested array from dot-notation keys.
     * Numeric segments (e.g. "user_level.0") are cast to integer keys.
     *
     * @param  array<string, string> $flat
     *
     * @return array<string|int, mixed>
     */
    private function unflatten(array $flat): array
    {
        $result = [];

        foreach ($flat as $dotKey => $value) {
            $segments = explode('.', $dotKey);
            $this->setNested($result, $segments, $value);
        }

        return $result;
    }

    /**
     * @param array<string|int, mixed> $arr
     * @param list<string>             $keys
     */
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
     *
     * @param array<string|int, mixed> $data
     */
    private function exportArray(array $data, int $indent = 1): string
    {
        $pad = str_repeat('    ', $indent);
        $closingPad = str_repeat('    ', $indent - 1);
        $lines = [];

        foreach ($data as $key => $value) {
            $keyStr = is_int($key) ? (string) $key : "'" . $this->escapeString((string) $key) . "'";

            if (is_array($value)) {
                $lines[] = "$pad$keyStr => " . $this->exportArray($value, $indent + 1) . ',';
                continue;
            }

            $lines[] = "$pad$keyStr => '" . $this->escapeString(is_scalar($value) ? (string) $value : '') . "',";
        }

        return "[\n" . implode("\n", $lines) . "\n{$closingPad}]";
    }

    private function escapeString(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
