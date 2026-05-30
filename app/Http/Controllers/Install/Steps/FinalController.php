<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class FinalController extends RequirementsController
{
    /** @var array<string, non-empty-string> */
    protected array $requirements = [
        'install' => 'checkInstallDir',
        'files' => 'checkFilesPermissions',
    ];

    /** @return view-string */
    protected function getView(): string
    {
        return 'install.steps.final';
    }

    /** @return array{requirement: mixed, message: mixed, result: string} */
    protected function checkInstallDir(): array
    {
        $requirement = __('install/install.final_install_not_accessible');
        $message = __('install/install.final_install_not_accessible_ok');
        $result = 'success';

        if (!$this->writeConfigFile()) {
            $this->fail = true;

            $message = __('install/install.final_install_not_accessible_need', ['file' => 'xgp-db-config.php']);
            $result = 'danger';
        }

        return [
            'requirement' => $requirement,
            'message' => $message,
            'result' => $result,
        ];
    }

    /** @return array{requirement: mixed, message: mixed, result: string} */
    protected function checkFilesPermissions(): array
    {
        $requirement = __('install/install.final_config_writable');
        $message = __('install/install.final_config_writable_ok');
        $result = 'success';

        $file = 'xgp-db-config.php';
        $disk = Storage::build([
            'driver' => 'local',
            'root' => config_path(),
        ]);

        $disk->setVisibility($file, 'private');

        if (
            !$disk->exists($file) ||
            $disk->getVisibility($file) === 'public'
        ) {
            $this->fail = true;

            $message = __('install/install.final_config_writable_need', ['file' => $file]);
            $result = 'danger';
        }

        return [
            'requirement' => $requirement,
            'message' => $message,
            'result' => $result,
        ];
    }

    private function writeConfigFile(): bool
    {
        $file = 'xgp-db-config.php';
        $disk = Storage::build([
            'driver' => 'local',
            'root' => config_path(),
        ]);

        if (!$disk->exists($file)) {
            return false;
        }

        $disk->setVisibility($file, 'public');

        $installData = "putenv('INSTALLED=true');\n";
        $installData .= "\n";

        $disk->append($file, $installData);

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        // wipe install progress so the flow can be started fresh next time
        session()->flush();

        return true;
    }
}
