<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Page;

class LanguagesController extends BaseController
{
    private string $alert = '';
    private string $current_file = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    private function runAction(): void
    {
        $action = filter_input_array(INPUT_POST);

        if ($action) {
            if (isset($action['file'])) {
                $this->doFileAction($action['file']);
            }

            if (isset($action['save']) && $action['save'] != '') {
                $this->doSaveAction($action['save']);
            }
        }
    }

    private function doFileAction(string $file): void
    {
        $this->current_file = $file;
    }

    private function doSaveAction(string $file_data): void
    {
        // get the file
        $file = LANG_PATH . DIRECTORY_SEPARATOR . $this->current_file;

        // open the file
        $fs = @fopen($file, 'w');

        if ($fs && $file_data != '') {
            fwrite($fs, $file_data);

            fclose($fs);
        }

        $this->alert = Administration::saveMessage('ok', $this->langs->line('le_all_ok_message'));
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->render(
                'admin.languages_view',
                array_merge(
                    $this->getFiles(),
                    $this->getContents(),
                    [
                        'edit_file' => $this->current_file,
                        'alert' => $this->alert ?? '',
                    ]
                )
            )
        );
    }

    private function getContents(): array
    {
        if (empty($this->current_file)) {
            return [
                'contents' => $contents ?? '',
            ];
        }

        $file = LANG_PATH . $this->current_file;

        // open the file
        $fs = @fopen($file, 'a+');
        $contents = '';

        if ($fs) {
            while (!feof($fs)) {
                $contents .= fgets($fs, 1024);
            }

            fclose($fs);
        }

        if (!$contents && $this->current_file != '') {
            $this->alert = Administration::saveMessage('error', $this->langs->line('le_all_error_reading'));
        }

        return [
            'contents' => $contents ?? '',
        ];
    }

    private function getFiles(): array
    {
        chdir(LANG_PATH);

        $langs_files = glob('{,*/,*/*/,*/*/*/}*.php', GLOB_BRACE);
        $lang_options = [];

        foreach ($langs_files as $file) {
            $lang_options[] = [
                'lang_file' => $file,
                'selected' => ($this->current_file == $file) ? 'selected = selected' : '',
            ];
        }

        return ['language_files' => $lang_options];
    }
}
