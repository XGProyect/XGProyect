<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Models\Adm\Backup;

class BackupController extends BaseController
{
    public const BACKUP_SETTINGS = [
        'auto_backup' => FILTER_UNSAFE_RAW,
    ];

    private Backup $backupModel;

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

        $this->backupModel = new Backup();

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
            $this->backupModel->performBackup();
        }

        // download or delete a file
        if ($file_actions) {
            if (in_array($file_actions['action'], ['download', 'delete']) &&
                $file_actions['file'] != null) {
                $this->{'do' . ucfirst($file_actions['action']) . 'Action'}($file_actions['file']);
            }
        }
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

        Functions::redirect('admin.php?page=backup');
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
