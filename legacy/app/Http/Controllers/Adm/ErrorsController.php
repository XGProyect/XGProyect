<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class ErrorsController extends BaseController
{
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
            'admin.errors',
            $this->processErrorsLogs()
        );
    }

    private function runAction(): void
    {
        $delete_all = filter_input(INPUT_GET, 'deleteall', FILTER_DEFAULT);
        $export_all = filter_input(INPUT_GET, 'exportall', FILTER_DEFAULT);

        if ($delete_all == 'yes') {
            $files = $this->getListOfLogFiles();

            if ($files != '') {
                foreach ($files as $file_name) {
                    unlink($file_name);
                }
            }
        }

        if ($export_all == 'yes') {
            $files = $this->getListOfLogFiles();

            if (!empty($files)) {
                header('Content-type: text/plain');
                header('Content-disposition: attachment; filename=xgproyect.log');
                readfile($files[0]);
                exit();
            }
        }
    }

    private function processErrorsLogs(): array
    {
        // list of log files
        $files = $this->getListOfLogFiles();
        $errorsList = [];
        $totalErrors = 0;

        if (!empty($files)) {
            $contents = file_get_contents($files[0]);

            if ($contents) {
                foreach (explode('"} ', $contents) as $singleError) {
                    $currentErrors = array_filter(explode(PHP_EOL, $singleError));

                    if (empty($currentErrors)) {
                        continue;
                    }

                    $errors['error_message'] = reset($currentErrors);

                    unset($currentErrors[key($currentErrors)]);

                    $errors['errors'] = $currentErrors;

                    $errorsList[] = $errors;

                    $totalErrors++;
                }
            }
        }

        return [
            'errorsList' => $errorsList,
            'totalErrors' => $totalErrors,
        ];
    }

    private function getListOfLogFiles(): array
    {
        return glob(storage_path('logs') . '/xgproyect.log');
    }
}
