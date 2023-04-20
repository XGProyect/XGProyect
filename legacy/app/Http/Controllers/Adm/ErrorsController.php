<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Page;

class ErrorsController extends BaseController
{
    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage(__('adm/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    private function runAction(): void
    {
        $delete_all = filter_input(INPUT_GET, 'deleteall', FILTER_DEFAULT);

        if ($delete_all == 'yes') {
            $files = $this->getListOfLogFiles();

            if ($files != '') {
                foreach ($files as $file_name) {
                    unlink($file_name);
                }
            }
        }
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->set(
                'adm/errors_view',
                $this->processErrorsLogs()
            )
        );
    }

    private function processErrorsLogs(): array
    {
        // list of log files
        $files = $this->getListOfLogFiles();
        $list_of_errors = [];
        $error_count = 0;

        if ($files != '') {
            foreach ($files as $file_name) {
                $contents = file_get_contents($file_name);

                if ($contents) {
                    $error_count++;

                    $error_columns = explode('|', $contents);

                    $list_of_errors[] = [
                        'user_ip' => $error_columns[1],
                        'error_type' => $error_columns[2],
                        'error_code' => $error_columns[3],
                        'error_message' => $error_columns[4],
                        'error_trace' => $error_columns[5],
                        'error_datetime' => $error_columns[7],
                        'alert_type' => ($error_columns[3] == 'E_ERROR' ? 'danger' : 'warning'),
                    ];
                }
            }
        }

        return [
            'errors_list' => $list_of_errors,
            'errors_list_resume' => strtr($this->langs->line('er_errors'), ['%s' => $error_count]),
        ];
    }

    private function getListOfLogFiles(): array
    {
        return glob(LOGS_PATH . '*.txt');
    }
}
