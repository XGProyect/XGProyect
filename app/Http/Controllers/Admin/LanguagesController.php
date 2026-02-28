<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LanguagesRequest;
use App\Services\Admin\LanguagesService;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class LanguagesController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
        private readonly LanguagesService $languagesService,
    ) {
    }

    public function index(Request $request): View | RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $currentFile = $request->string('file')->toString();
        $translations = [];

        if ($currentFile) {
            $translations = $this->languagesService->loadTranslations($currentFile);

            if ($translations === null) {
                return redirect()
                    ->route('admin.languages')
                    ->with('danger', __('admin/languages.le_all_error_reading'));
            }
        }

        return view('admin.languages', [
            'groupedFiles' => $this->languagesService->getGroupedFiles(),
            'currentFile' => $currentFile,
            'translations' => $translations,
        ]);
    }

    public function update(LanguagesRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var array{file: string, translations: list<array{key: string, value: string}>} $data */
        $data = $request->validated();

        $this->languagesService->saveTranslations($data['file'], $data['translations']);

        session()->flash('success', __('admin/languages.le_all_ok_message'));

        return redirect()->route('admin.languages', ['file' => $data['file']]);
    }
}
