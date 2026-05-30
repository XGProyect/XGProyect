<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use App\Services\InstallRequirements;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class RequirementsController extends BaseController
{
    /** @var array<string, non-empty-string> */
    protected array $requirements = [
        'php' => 'checkPhpVersion',
        'mysql' => 'checkMySQLVersion',
        'files' => 'checkFilesPermissions',
        'extensions' => 'checkPhpExtensions',
    ];

    protected bool $fail = false;

    public function __invoke(Request $request): View | Factory
    {
        $route = $request->route();

        session(['last_step' => $route !== null ? $route->getName() : null]);

        return view(
            $this->getView(),
            [
                'testResults' => $this->checkRequirements(),
                'fail' => $this->fail,
            ]
        );
    }

    /** @return view-string */
    protected function getView(): string
    {
        return 'install.steps.requirements';
    }

    /** @return array{requirement: mixed, message: mixed, result: string} */
    protected function checkPhpVersion(): array
    {
        $currentVersion = PHP_VERSION;

        if (!InstallRequirements::isSupportedPhpVersion($currentVersion)) {
            $this->fail = true;

            return [
                'requirement' => __('install/install.php_version_check', ['version' => InstallRequirements::MINIMUM_PHP_VERSION]),
                'message' => __('install/install.php_version_need', ['version' => InstallRequirements::MINIMUM_PHP_VERSION]),
                'result' => 'danger',
            ];
        }

        return [
            'requirement' => __('install/install.php_version_check', ['version' => InstallRequirements::MINIMUM_PHP_VERSION]),
            'message' => __('install/install.php_version_current', ['php' => $currentVersion]),
            'result' => 'success',
        ];
    }

    /** @return array{requirement: mixed, message: mixed, result: string} */
    protected function checkMySQLVersion(): array
    {
        return [
            'requirement' => __('install/install.mysql_check', ['version' => InstallRequirements::MINIMUM_DATABASE_VERSION]),
            'message' => __('install/install.mysql_check_current'),
            'result' => 'warning',
        ];
    }

    /** @return array{requirement: mixed, message: mixed, result: string} */
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

    /** @return array{requirement: mixed, message: mixed, result: string} */
    protected function checkPhpExtensions(): array
    {
        $notLoaded = InstallRequirements::missingPhpExtensions();

        if ($notLoaded !== []) {
            $this->fail = true;

            return [
                'requirement' => __('install/install.php_ext_check'),
                'message' => __('install/install.php_ext_check_need', ['ext' => implode(', ', $notLoaded)]),
                'result' => 'danger',
            ];
        }

        return [
            'requirement' => __('install/install.php_ext_check'),
            'message' => __('install/install.php_ext_check_ok'),
            'result' => 'success',
        ];
    }

    /**
     * @return array<string, array{requirement: mixed, message: mixed, result: string}>
     */
    private function checkRequirements(): array
    {
        $results = [];

        foreach ($this->requirements as $requirement => $callback) {
            $results[$requirement] = $this->$callback();
        }

        return $results;
    }
}
