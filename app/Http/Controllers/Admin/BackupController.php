<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;

class BackupController extends BaseController
{
    public const BACKUP_SETTINGS = [
        'auto_backup' => FILTER_UNSAFE_RAW,
    ];

    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.backup',
            array_merge(
                $this->getBackupSettings(),
                $this->getBackupList()
            )
        );
    }

    private function runAction(): void
    {
        $save = filter_input(INPUT_POST, 'save');
        $backup = filter_input(INPUT_POST, 'backup');
        $file_actions = filter_input_array(INPUT_GET, [
            'action' => FILTER_UNSAFE_RAW,
            'file' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'isValidFile'],
            ],
        ]);

        // save form
        if ($save) {
            $data = filter_input_array(INPUT_POST, self::BACKUP_SETTINGS, true);

            foreach ($data as $option => $value) {
                Options::getInstance()->write($option, ($value == 'on' ? 1 : 0));
            }
        }

        // create a new backup
        if ($backup) {
            $this->performBackup();
        }

        // download or delete a file
        if ($file_actions) {
            if (in_array($file_actions['action'], ['download', 'delete']) &&
                $file_actions['file'] != null) {
                $this->{'do' . ucfirst($file_actions['action']) . 'Action'}($file_actions['file']);
            }
        }
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

        $fileName = 'db-backup-' . date('Ymd') . '-' . time() . '-' . sha1(join(',', $tables)) . '.sql';
        $handle = fopen(storage_path('backups') . DIRECTORY_SEPARATOR . $fileName, 'w+');
        fwrite($handle, $dump);
        fclose($handle);
    }

    private function doDownloadAction(string $file_name): void
    {
        $to_download = storage_path('backups') . DIRECTORY_SEPARATOR . $file_name;

        if (file_exists($to_download)) {
            header('Content-type: text/plain');
            header('Content-disposition: attachment; filename=' . $file_name);
            readfile($to_download);
            exit();
        }
    }

    private function doDeleteAction(string $file_name): void
    {
        $to_delete = storage_path('backups') . DIRECTORY_SEPARATOR . $file_name;

        if (file_exists($to_delete)) {
            unlink($to_delete);
        }

        Functions::redirect('/admin/backup');
    }

    private function getBackupSettings(): array
    {
        return $this->setChecked(
            array_filter(
                Options::getInstance()->get(),
                function ($key) {
                    return array_key_exists($key, self::BACKUP_SETTINGS);
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    private function setChecked(array $settings): array
    {
        foreach ($settings as $key => $value) {
            $settings[$key] = $value == 1 ? 'checked="checked"' : '';
        }

        return $settings;
    }

    private function getBackupList(): array
    {
        $backup_list = [];

        chdir(storage_path('backups'));
        $files = glob('*.sql');

        if ($files != '') {
            foreach ($files as $file_name) {
                $backup_list[] = [
                    'file_name' => $this->formatFileName($file_name),
                    'file_size' => Format::prettyBytes(filesize($file_name)),
                    'full_file_name' => $file_name,
                ];
            }
        }

        krsort($backup_list);

        return [
            'backup_list' => $backup_list,
        ];
    }

    private function formatFileName(string $file_name): string
    {
        $matches = [];
        preg_match('/db-backup-(?:[0-9]+)-([0-9]+)-(?:[a-zA-Z0-9]+)\.sql/', $file_name, $matches);

        return Timing::formatExtendedDate($matches[1]);
    }

    private function isValidFile(string $file_name): string
    {
        if ((bool) preg_match('/db-backup-(?:[0-9]+)-([0-9]+)-(?:[a-zA-Z0-9]+)\.sql/', $file_name, $matches) !== false) {
            return $file_name;
        }

        return '';
    }
}
