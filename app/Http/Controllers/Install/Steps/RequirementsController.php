<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class RequirementsController extends BaseController
{
    protected array $requirements = [
        'php' => 'checkPhpVersion',
        'mysql' => 'checkMySQLVersion',
        'files' => 'checkFilesPermissions',
        'extensions' => 'checkPhpExtensions',
    ];

    protected bool $fail = false;

    public function __invoke(Request $request): View | Factory
    {
        session(['last_step' => $request->route()->getName()]);

        return view(
            $this->getView(),
            [
                'testResults' => $this->checkRequirements(),
                'fail' => $this->fail,
            ]
        );
    }

    protected function getView(): string
    {
        return 'install.steps.requirements';
    }

    protected function checkPhpVersion(): array
    {
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $this->fail = true;

            return [
                'requirement' => __('install/install.php_version_check'),
                'message' => __('install/install.php_version_need'),
                'result' => 'danger',
            ];
        }

        return [
            'requirement' => __('install/install.php_version_check'),
            'message' => __('install/install.php_version_current', ['php' => PHP_VERSION]),
            'result' => 'success',
        ];
    }

    protected function checkMySQLVersion(): array
    {
        return [
            'requirement' => __('install/install.mysql_check'),
            'message' => __('install/install.mysql_check_current'),
            'result' => 'warning',
        ];
    }

    protected function checkFilesPermissions(): array
    {
        $requirement = __('install/install.config_writable');
        $message = __('install/install.config_writable_ok');
        $result = 'success';

        $file = 'xgp-db-config.php';
        $disk = Storage::build([
            'driver' => 'local',
            'root' => config_path(),
        ]);

        if (!$disk->exists($file)) {
            $this->fail = true;

            $message = __('install/install.config_writable_need', ['file' => $file]);
            $result = 'danger';
        }

        $disk->setVisibility($file, 'public');

        return [
            'requirement' => $requirement,
            'message' => $message,
            'result' => $result,
        ];
    }

    protected function checkPhpExtensions(): array
    {
        $notLoaded = [];
        $extensions = [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
        ];

        foreach ($extensions as $extension) {
            if (!@extension_loaded($extension)) {
                $notLoaded[] = $extension;
            }
        }

        if (count($notLoaded) > 0) {
            $this->fail = true;

            return [
                'requirement' => __('install/install.php_ext_check'),
                'message' => __('install/install.php_ext_check_need', ['ext' => join(', ', $notLoaded)]),
                'result' => 'danger',
            ];
        }

        return [
            'requirement' => __('install/install.php_ext_check'),
            'message' => __('install/install.php_ext_check_ok'),
            'result' => 'success',
        ];
    }

    private function checkRequirements(): array
    {
        $results = [];

        foreach ($this->requirements as $requirement => $callback) {
            $results[$requirement] = $this->$callback();
        }

        return $results;
    }
}
