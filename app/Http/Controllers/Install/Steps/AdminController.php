<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use App\Http\Requests\Install\AdminRequest;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Models\Home\Register;

class AdminController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        // @phpstan-ignore-next-line
        session(['last_step' => $request->route()->getName()]);

        return view(
            'install.steps.admin',
            [
                'hideForm' => session()->has('admin_create_success') && session('admin_create_success', false),
            ]
        );
    }

    public function doCheck(AdminRequest $request): RedirectResponse
    {
        try {
            // create new user + attach new planet
            (new Register())->createNewUser(
                $request,
                [
                    'galaxy' => 1,
                    'system' => 1,
                    'planet' => 1,
                ],
                true
            );
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('danger', __('install/install.admin_create_fail'));
        }

        session()->put('admin_create_success', true);

        return back()
            ->with('success', __('install/install.admin_create_success'));
    }
}
