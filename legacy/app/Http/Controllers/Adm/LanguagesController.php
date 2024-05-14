<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class LanguagesController extends BaseController
{
    private string $currentFile = '';
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
            'admin.languages',
            array_merge(
                $this->getFiles(),
                $this->getContents(),
                [
                    'editFile' => $this->currentFile,
                ]
            )
        );
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
        $this->currentFile = $file;
    }

    private function doSaveAction(string $fileData): void
    {
        $fs = @fopen(lang_path($this->currentFile), 'w');

        if ($fs && $fileData != '') {
            fwrite($fs, $fileData);

            fclose($fs);
        }

        session()->flash('success', __('admin/languages.le_all_ok_message'));
    }

    private function getContents(): array
    {
        if (empty($this->currentFile)) {
            return [
                'contents' => '',
            ];
        }

        $file = lang_path($this->currentFile);

        // open the file
        $fs = @fopen($file, 'a+');
        $contents = '';

        if ($fs) {
            while (!feof($fs)) {
                $contents .= fgets($fs, 1024);
            }

            fclose($fs);
        }

        if (!$contents && $this->currentFile != '') {
            session()->flash('error', __('admin/languages.le_all_error_reading'));
        }

        return [
            'contents' => $contents,
        ];
    }

    private function getFiles(): array
    {
        chdir(lang_path());

        $langFiles = glob('{,*/,*/*/,*/*/*/}*.php', GLOB_BRACE);
        $langOptions = [];

        foreach ($langFiles as $file) {
            $langOptions[] = [
                'lang_file' => $file,
                'selected' => ($this->currentFile == $file) ? 'selected = selected' : '',
            ];
        }

        return ['language_files' => $langOptions];
    }
}
