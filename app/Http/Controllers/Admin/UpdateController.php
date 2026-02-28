<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Core\Options;

class UpdateController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public static function make(): static
    {
        return new static(new AdministrationService(new SettingsService()));
    }

    public function __invoke(Request $request): View | RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $systemVersion = config('version.files');
        $dbVersion = Options::getInstance()->get('version');
        $subTitle = sprintf(__('admin/update.up_sub_title'), $dbVersion, $systemVersion);

        if ($systemVersion === $dbVersion) {
            session()->flash('danger', __('admin/update.up_no_update_required'));

            return view('admin.update', [
                'continue' => false,
                'up_sub_title' => $subTitle,
            ]);
        }

        if ($request->isMethod('post')) {
            return $this->handlePost($request, $dbVersion, $subTitle);
        }

        return view('admin.update', [
            'continue' => true,
            'up_sub_title' => $subTitle,
        ]);
    }

    private function handlePost(Request $request, string $dbVersion, string $subTitle): View | RedirectResponse
    {
        if (!$this->checkVersion()) {
            session()->flash('warning', __('admin/update.up_no_version_file'));

            return view('admin.update', [
                'continue' => false,
                'up_sub_title' => $subTitle,
            ]);
        }

        $demo = $request->boolean('demo_mode');
        $output = $this->startUpdate($dbVersion, $demo);

        if ($demo) {
            session()->flash('success', __('admin/update.up_success'));

            return view('admin.update_result', [
                'up_sub_title' => $subTitle,
                'result' => print_r($output, true),
            ]);
        }

        return redirect('admin/update')->with('success', __('admin/update.up_success'));
    }

    private function checkVersion(): bool
    {
        return file_exists($this->updatePath() . 'update_common.php');
    }

    private function startUpdate(string $dbVersion, bool $demo): array
    {
        $updatesDir = opendir($this->updatePath());
        $exceptions = ['.', '..', '.htaccess', 'index.html', '.DS_Store', 'update_common.php'];
        $filesToRead = [];
        $numericDbVersion = strtr($dbVersion, ['v' => '', '.' => '']);

        while (($file = readdir($updatesDir)) !== false) {
            if (in_array($file, $exceptions)) {
                continue;
            }

            $fileVersion = strtr($file, ['update_' => '', '.php' => '']);

            if ($numericDbVersion >= $fileVersion) {
                continue;
            }

            $filesToRead[] = $fileVersion;
        }

        closedir($updatesDir);
        asort($filesToRead);
        $filesToRead[] = 'common';

        $output = [];

        foreach ($filesToRead as $version) {
            $output = array_merge($output, $this->executeFile($version, $demo));
        }

        return $output;
    }

    private function executeFile(string $version, bool $demo): array
    {
        $updatePath = $this->updatePath() . 'update_' . $version . '.php';
        $queries = [];

        require_once $updatePath;

        $output = [];

        foreach ($queries as $query) {
            $output[] = $demo ? $query : DB::unprepared($query);
        }

        return $output;
    }

    private function updatePath(): string
    {
        return base_path('updates') . DIRECTORY_SEPARATOR;
    }
}
