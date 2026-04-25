<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use App\Http\Requests\Install\AdminRequest;
use App\Models\Planets;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\UserRanksEnumerator;
use Xgp\App\Libraries\PlanetLib;

class AdminController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        $route = $request->route();

        session(['last_step' => $route !== null ? $route->getName() : null]);

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
            DB::beginTransaction();

            $newUser = User::create(array_merge(
                $request->validated(),
                [
                    'name' => $request->validated()['username'],
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'lastip' => $request->ip(),
                    'ip_at_reg' => $request->ip(),
                    'agent' => $request->header('User-Agent'),
                    'current_page' => $request->getRequestUri(),
                    'register_time' => time(),
                    'onlinetime' => time(),
                    'authlevel' => UserRanksEnumerator::ADMIN,
                ]
            ));

            $newUser->preferences()->create();
            $newUser->premium()->create();
            $newUser->research()->create();
            $newUser->stats()->create();

            (new PlanetLib())->setNewPlanet(1, 1, 1, $newUser->id, '', true);

            User::where('id', $newUser->id)->update([
                'home_planet_id' => Planets::where('planet_user_id', $newUser->id)->value('planet_id'),
                'current_planet' => Planets::where('planet_user_id', $newUser->id)->value('planet_id'),
                'galaxy' => 1,
                'system' => 1,
                'planet' => 1,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            return back()
                ->withInput()
                ->with('danger', __('install/install.admin_create_fail'));
        }

        session()->put('admin_create_success', true);

        return back()
            ->with('success', __('install/install.admin_create_success'));
    }
}
