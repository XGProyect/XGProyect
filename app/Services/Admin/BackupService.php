<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Support\Collection;
use PDO;
use PDOStatement;
use Xgp\App\Core\Options;

class BackupService
{
    private const FILE_PATTERN = '/^db-backup-[0-9]+-([0-9]+)-[a-zA-Z0-9]+\.sql$/';

    public function __construct(
        private readonly string $storagePath = '',
        private readonly ?PDO $pdo = null,
    ) {
    }

    public function isValidFileName(string $fileName): bool
    {
        return (bool) preg_match(self::FILE_PATTERN, $fileName);
    }

    /**
     * @return array<string, bool>
     */
    public function getSettings(): array
    {
        return [
            'auto_backup' => Options::getInstance()->get('auto_backup') == 1,
        ];
    }

    public function saveSettings(bool $autoBackup): void
    {
        Options::getInstance()->write('auto_backup', $autoBackup ? 1 : 0);
    }

    /**
     * @return Collection<int, array{file_name: string, file_size: string, full_file_name: string}>
     */
    public function getBackupList(): Collection
    {
        return collect(glob($this->backupPath('*.sql')) ?: [])
            ->map(function (string $filePath) {
                $fileName = basename($filePath);

                return [
                    'file_name' => $this->formatFileName($fileName),
                    'file_size' => $this->prettyBytes((int) filesize($filePath)),
                    'full_file_name' => $fileName,
                ];
            })
            ->sortByDesc(fn (array $item) => $this->extractTimestamp($item['full_file_name']))
            ->values();
    }

    public function createBackup(): string
    {
        $pdo = $this->pdo;

        if ($pdo === null) {
            /** @var \Illuminate\Database\Connection $db */
            $db = app('db');
            $pdo = $db->getPdo();
        }

        $tables = [];
        $tablesStmt = $pdo->query('SHOW TABLES');
        assert($tablesStmt instanceof \PDOStatement);

        foreach ($tablesStmt->fetchAll(\PDO::FETCH_NUM) as $row) {
            $tables[] = $row[0];
        }

        $dump = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $quotedTable = '`' . $table . '`';

            $rowsStmt = $pdo->query('SELECT * FROM ' . $quotedTable);
            assert($rowsStmt instanceof PDOStatement);
            $rows = $rowsStmt->fetchAll(PDO::FETCH_NUM);

            $colStmt = $pdo->query('SELECT * FROM ' . $quotedTable . ' LIMIT 0');
            assert($colStmt instanceof PDOStatement);
            $numFields = $colStmt->columnCount();

            $dump .= 'DROP TABLE IF EXISTS ' . $quotedTable . ';';

            $createStmt = $pdo->query('SHOW CREATE TABLE ' . $quotedTable);
            assert($createStmt instanceof PDOStatement);
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            assert(is_array($createRow) && isset($createRow[1]) && is_string($createRow[1]));
            $dump .= "\n\n" . $createRow[1] . ";\n\n";

            foreach ($rows as $row) {
                $dump .= 'INSERT INTO ' . $quotedTable . ' VALUES(';

                for ($j = 0; $j < $numFields; $j++) {
                    if ($row[$j] === null) {
                        $dump .= 'NULL';

                        if ($j < ($numFields - 1)) {
                            $dump .= ',';
                        }

                        continue;
                    }

                    $value = addslashes((string) $row[$j]);
                    $value = str_replace("\n", '\\n', $value);
                    $dump .= '"' . $value . '"';

                    if ($j < ($numFields - 1)) {
                        $dump .= ',';
                    }
                }

                $dump .= ");\n";
            }

            $dump .= "\n\n\n";
        }

        $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = 'db-backup-' . date('Ymd') . '-' . time() . '-' . sha1(implode(',', $tables)) . '.sql';

        file_put_contents($this->backupPath($fileName), $dump);

        return $fileName;
    }

    public function deleteBackup(string $fileName): void
    {
        $path = $this->backupPath($fileName);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function backupPath(string $file = ''): string
    {
        $base = $this->storagePath !== '' ? $this->storagePath : storage_path('backups');

        return $file !== '' ? $base . DIRECTORY_SEPARATOR . $file : $base;
    }

    protected function formatFileName(string $fileName): string
    {
        $format = Options::getInstance()->get('date_format_extended');
        $format = is_string($format) ? $format : 'Y-m-d H:i:s';

        return date($format, $this->extractTimestamp($fileName));
    }

    private function extractTimestamp(string $fileName): int
    {
        preg_match(self::FILE_PATTERN, $fileName, $matches);

        return (int) ($matches[1] ?? 0);
    }

    private function prettyBytes(int | float $bytes, int $precision = 2): string
    {
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
