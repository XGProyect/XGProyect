<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class ErrorsController extends BaseController
{
    public function __invoke(): void
    {
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
