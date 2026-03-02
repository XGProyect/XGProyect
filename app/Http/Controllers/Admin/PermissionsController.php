<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PermissionsRequest;
use App\Services\Admin\PermissionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class PermissionsController extends BaseController
{
    public function __construct(
        private readonly PermissionsService $permissionsService,
    ) {
    }

    public function index(): View
    {
        return view('admin.permissions', $this->permissionsService->buildViewData());
    }

    public function save(PermissionsRequest $request): RedirectResponse
    {
        $this->permissionsService->updatePermissions($request->except(['_token', '_method']));

        session()->flash('success', __('admin/permissions.pr_all_ok_message'));

        return redirect()->route('admin.permissions');
    }
}
