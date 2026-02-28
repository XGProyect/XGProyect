<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BackupRequest;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Core\Options;

class BackupController extends BaseController
{
    private const FILE_PATTERN = '/^db-backup-[0-9]+-([0-9]+)-[a-zA-Z0-9]+\.sql$/';

    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.backup', array_merge(
            $this->getBackupSettings(),
            $this->getBackupList(),
        ));
    }

    public function save(BackupRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        Options::getInstance()->write('auto_backup', $request->has('auto_backup') ? 1 : 0);

        return redirect()->route('admin.backup');
    }

    public function create(): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->performBackup();

        return redirect()->route('admin.backup');
    }

    public function download(string $file): Response
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        abort_unless($this->isValidFileName($file), 404);

        $path = storage_path('backups' . DIRECTORY_SEPARATOR . $file);

        abort_unless(file_exists($path), 404);

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }

    public function destroy(string $file): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        abort_unless($this->isValidFileName($file), 404);

        $path = storage_path('backups' . DIRECTORY_SEPARATOR . $file);

        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->route('admin.backup');
    }

    private function performBackup(): void
    {
        $pdo = DB::getPdo();
        $tables = [];

        foreach ($pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM) as $row) {
            $tables[] = $row[0];
        }

        $dump = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $rows = $pdo->query('SELECT * FROM ' . $table)->fetchAll(\PDO::FETCH_NUM);
            $stmt = $pdo->query('SELECT * FROM ' . $table . ' LIMIT 0');
            $numFields = $stmt->columnCount();

            $dump .= 'DROP TABLE IF EXISTS ' . $table . ';';
            $createRow = $pdo->query('SHOW CREATE TABLE ' . $table)->fetch(\PDO::FETCH_NUM);
            $dump .= "\n\n" . $createRow[1] . ";\n\n";

            foreach ($rows as $row) {
                $dump .= 'INSERT INTO ' . $table . ' VALUES(';

                for ($j = 0; $j < $numFields; $j++) {
                    if ($row[$j] === null) {
                        $dump .= 'NULL';
                    } else {
                        $value = addslashes((string) $row[$j]);
                        $value = str_replace("\n", '\\n', $value);
                        $dump .= '"' . $value . '"';
                    }

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

        file_put_contents(storage_path('backups' . DIRECTORY_SEPARATOR . $fileName), $dump);
    }

    private function getBackupSettings(): array
    {
        return [
            'auto_backup' => Options::getInstance()->get('auto_backup') == 1 ? 'checked="checked"' : '',
        ];
    }

    private function getBackupList(): array
    {
        $backupPath = storage_path('backups');
        $files = glob($backupPath . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        $backupList = array_map(function (string $filePath) {
            $fileName = basename($filePath);

            return [
                'file_name' => $this->formatFileName($fileName),
                'file_size' => $this->prettyBytes((int) filesize($filePath)),
                'full_file_name' => $fileName,
            ];
        }, $files);

        krsort($backupList);

        return ['backup_list' => array_values($backupList)];
    }

    private function formatFileName(string $fileName): string
    {
        preg_match(self::FILE_PATTERN, $fileName, $matches);

        return date((string) Options::getInstance()->get('date_format_extended'), (int) ($matches[1] ?? 0));
    }

    private function isValidFileName(string $fileName): bool
    {
        return (bool) preg_match(self::FILE_PATTERN, $fileName);
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
