<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LanguagesRequest;
use App\Services\Admin\LanguagesService;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class LanguagesController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
        private readonly LanguagesService $languagesService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $currentFile = $request->query('file', '');
        $translations = [];

        if ($currentFile) {
            $translations = $this->languagesService->loadTranslations($currentFile);

            if (empty($translations)) {
                session()->flash('error', __('admin/languages.le_all_error_reading'));
                $currentFile = '';
            }
        }

        return view('admin.languages', [
            'language_files' => $this->languagesService->getFiles(),
            'currentFile' => $currentFile,
            'translations' => $translations,
        ]);
    }

    public function update(LanguagesRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $request->validated();

        $this->languagesService->saveTranslations($data['file'], $data['translations']);

        session()->flash('success', __('admin/languages.le_all_ok_message'));

        return redirect()->route('admin.languages', ['file' => $data['file']]);
    }
}
