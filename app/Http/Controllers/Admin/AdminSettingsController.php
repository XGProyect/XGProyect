<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class AdminSettingsController extends BaseController
{
    public function __construct(
        protected readonly SettingsService $settings,
    ) {
    }

    /**
     * @param view-string $view
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = []): View
    {
        return view($view, $data);
    }

    protected function saved(string $route, string $messageKey): RedirectResponse
    {
        return redirect()->route($route)->with('success', __($messageKey));
    }
}
