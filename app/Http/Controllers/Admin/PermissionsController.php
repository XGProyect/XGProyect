<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PermissionsRequest;
use App\Services\Admin\PermissionsService;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class PermissionsController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
        private readonly PermissionsService $permissionsService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.permissions', $this->permissionsService->buildViewData());
    }

    public function save(PermissionsRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->permissionsService->updatePermissions($request->except(['_token', '_method']));

        session()->flash('success', __('admin/permissions.pr_all_ok_message'));

        return redirect()->route('admin.permissions');
    }
}
