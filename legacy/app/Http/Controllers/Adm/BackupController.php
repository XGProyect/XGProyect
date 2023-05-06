<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
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

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->backupModel = new Backup();

        $this->runAction();

        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
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
                Functions::updateConfig($option, ($value == 'on' ? 1 : 0));
            }
        }

        // create a new backup
        if ($backup) {
            $this->backupModel->performBackup();
        }

        // download or delete a file
        if ($file_actions) {
            if (in_array($file_actions['action'], ['download', 'delete'])
                && $file_actions['file'] != null) {
                $this->{'do' . ucfirst($file_actions['action']) . 'Action'}($file_actions['file']);
            }
        }
    }

    /**
     * Download the provided file
     *
     * @param string $file_name
     * @return void
     */
    private function doDownloadAction(string $file_name): void
    {
        $to_download = BACKUP_PATH . $file_name;

        if (file_exists($to_download)) {
            header('Content-type: text/plain');
            header('Content-disposition: attachment; filename=' . $file_name);
            readfile($to_download);
            exit();
        }
    }

    /**
     * Delete the provided file
     *
     * @param string $file_name
     * @return void
     */
    private function doDeleteAction(string $file_name): void
    {
        $to_delete = BACKUP_PATH . $file_name;

        if (file_exists($to_delete)) {
            unlink($to_delete);
        }

        Functions::redirect('admin.php?page=backup');
    }

    private function buildPage(): void
    {
        Template::getInstance()->view(
            'admin.backup',
            array_merge(
                $this->getBackupSettings(),
                $this->getBackupList()
            )
        );
    }

    private function getBackupSettings(): array
    {
        return $this->setChecked(
            array_filter(
                Functions::readConfig('', true),
                function ($key) {
                    return array_key_exists($key, self::BACKUP_SETTINGS);
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    /**
     * Coverts the setting value from an int to a "checked"
     *
     * @param array $settings
     * @return array
     */
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

        // list of backup files
        chdir(BACKUP_PATH);
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

    /**
     * Format the file name to get the current date as name
     *
     * @param string $file_name
     * @return string
     */
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
