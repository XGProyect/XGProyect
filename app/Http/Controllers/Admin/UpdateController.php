<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateRequest;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
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

    public function index(): View
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

        return view('admin.update', [
            'continue' => true,
            'up_sub_title' => $subTitle,
        ]);
    }

    public function run(UpdateRequest $request): View | RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $systemVersion = config('version.files');
        $dbVersion = Options::getInstance()->get('version');
        $subTitle = sprintf(__('admin/update.up_sub_title'), $dbVersion, $systemVersion);

        if (!$this->checkVersion()) {
            session()->flash('warning', __('admin/update.up_no_version_file'));

            return view('admin.update', [
                'continue' => false,
                'up_sub_title' => $subTitle,
            ]);
        }

        $demo = isset($request->validated()['demo_mode']);
        $output = $this->startUpdate($dbVersion, $demo);

        if ($demo) {
            session()->flash('success', __('admin/update.up_success'));

            return view('admin.update_result', [
                'up_sub_title' => $subTitle,
                'result' => print_r($output, true),
            ]);
        }

        return redirect()->route('admin.update')->with('success', __('admin/update.up_success'));
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
