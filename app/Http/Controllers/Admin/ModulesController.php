<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ModulesRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ModulesController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.modules', [
            'modules' => explode(';', $this->settings->getString('modules')),
            'module_names' => (array) __('admin/modules.mdl_modules'),
        ]);
    }

    public function update(ModulesRequest $request): RedirectResponse
    {
        $this->settings->write('modules', implode(';', $request->toValues()));

        return $this->saved('admin.modules', 'admin/modules.mdl_all_ok_message');
    }
}
