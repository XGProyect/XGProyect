<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ChangelogRequest;
use App\Models\Changelog;
use App\Services\Admin\ChangelogService;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ChangelogController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
        private readonly ChangelogService $changelogService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.changelog', [
            'changelog' => $this->changelogService->getEntries(),
        ]);
    }

    public function create(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view(
            'admin.changelog_form',
            $this->changelogService->getFormData('add')
        );
    }

    public function store(ChangelogRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $request->validated();

        $this->changelogService->create(
            $data['changelog_language'],
            $data['changelog_version'],
            $data['changelog_date'],
            $data['text'],
        );

        session()->flash('success', __('admin/changelog.ch_action_add_done'));

        return redirect()->route('admin.changelog');
    }

    public function edit(Changelog $changelog): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view(
            'admin.changelog_form',
            $this->changelogService->getFormData('edit', $changelog)
        );
    }

    public function update(ChangelogRequest $request, Changelog $changelog): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $request->validated();

        $this->changelogService->update(
            $changelog,
            $data['changelog_language'],
            $data['changelog_version'],
            $data['changelog_date'],
            $data['text'],
        );

        session()->flash('success', __('admin/changelog.ch_action_edit_done'));

        return redirect()->route('admin.changelog');
    }

    public function destroy(Changelog $changelog): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->changelogService->delete($changelog);

        session()->flash('success', __('admin/changelog.ch_action_delete_done'));

        return redirect()->route('admin.changelog');
    }
}
