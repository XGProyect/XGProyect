<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LanguagesSaveRequest;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\Finder\SplFileInfo;

class LanguagesController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $currentFile = $request->query('file', '');
        $contents = '';

        if ($currentFile) {
            $path = lang_path($currentFile);

            if (File::exists($path)) {
                $contents = File::get($path);
            } else {
                session()->flash('error', __('admin/languages.le_all_error_reading'));
            }
        }

        $langFiles = collect(File::allFiles(lang_path()))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => $file->getRelativePathname())
            ->sort()
            ->values()
            ->all();

        return view('admin.languages', [
            'language_files' => $langFiles,
            'currentFile'    => $currentFile,
            'contents'       => $contents,
        ]);
    }

    public function save(LanguagesSaveRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $request->validated();
        $filePath = realpath(lang_path($data['file']));
        $langDir = realpath(lang_path());

        if (!$filePath || !str_starts_with($filePath, $langDir . DIRECTORY_SEPARATOR)) {
            abort(403);
        }

        File::put($filePath, $data['save']);

        session()->flash('success', __('admin/languages.le_all_ok_message'));

        return redirect()->route('admin.languages', ['file' => $data['file']]);
    }
}
