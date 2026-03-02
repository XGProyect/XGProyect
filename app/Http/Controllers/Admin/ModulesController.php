<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ModulesRequest;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ModulesController extends AdminSettingsController
{
    public function __construct(AdministrationService $administrationService, SettingsService $settings)
    {
        parent::__construct($administrationService, $settings);
    }

    public function index(): View
    {
        $this->authorize();

        return $this->view('admin.modules', [
            'modules' => explode(';', $this->settings->getString('modules')),
            'module_names' => (array) __('admin/modules.mdl_modules'),
        ]);
    }

    public function update(ModulesRequest $request): RedirectResponse
    {
        $this->authorize();

        $this->settings->write('modules', implode(';', $request->toValues()));

        return $this->saved('admin.modules', 'admin/modules.mdl_all_ok_message');
    }
}
