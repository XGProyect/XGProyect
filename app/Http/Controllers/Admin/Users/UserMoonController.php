<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\Users\UserPlanetRequest;
use App\Http\Traits\Admin\Users\UserPlanetTrait;
use App\Models\User;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Options;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UserMoonController extends BaseController
{
    use UserPlanetTrait;

    private const AUTH_MODULE = UsersController::class;

    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function showMoons(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        return view('admin.users_moons', [
            'user' => $user,
            'moons' => $this->getMoons($user->id),
        ]);
    }

    public function showMoon(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $moonData = $this->getPlanetData($moon, PlanetTypesEnumerator::MOON);
        if (!$moonData) {
            abort(404);
        }

        return view('admin.users_moon_edit', [
            'user' => $user,
            'moon' => $this->preparePlanetViewData($moonData, $this->dateFormatExtended()),
            'all_users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'images' => $this->getPlanetImages(),
            'percent_options' => $this->percentOptions(),
            'queue_options' => $this->buildProcessQueue((string) ($moonData->planet_b_building_id ?? '')),
        ]);
    }

    public function updateMoon(UserPlanetRequest $request, User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $this->savePlanetData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    public function showMoonBuildings(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        return view('admin.users_planet_buildings', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'buildings' => $this->buildBuildingsList($this->getBuildingsData($moon), PlanetTypesEnumerator::MOON),
        ]);
    }

    public function updateMoonBuildings(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $this->saveBuildingsData($request, $moon, PlanetTypesEnumerator::MOON);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.buildings', [$user->id, $moon]);
    }

    public function showMoonShips(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        return view('admin.users_planet_ships', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'ships' => $this->buildShipsList($this->getShipsData($moon)),
        ]);
    }

    public function updateMoonShips(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $this->saveShipsData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.ships', [$user->id, $moon]);
    }

    public function showMoonDefenses(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        return view('admin.users_planet_defenses', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'defenses' => $this->buildDefensesList($this->getDefensesData($moon), PlanetTypesEnumerator::MOON),
        ]);
    }

    public function updateMoonDefenses(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $this->saveDefensesData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.defenses', [$user->id, $moon]);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function softDeleteMoon(User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        /** @phpstan-ignore constant.notFound */
        $destroyTime = time() + (PLANETS_LIFE_TIME * 3600);

        $prefix = DB::getTablePrefix();

        DB::statement(
            "UPDATE `{$prefix}planets` AS m
             JOIN `{$prefix}users` AS u ON u.`id` = m.`planet_user_id`
             SET m.`planet_destroyed` = ?,
                 u.`current_planet` = u.`home_planet_id`
             WHERE m.`planet_id` = ? AND m.`planet_type` = '3'",
            [$destroyTime, $moon]
        );

        session()->flash('success', __('admin/users.us_moon_soft_deleted'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function hardDeleteMoon(User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(self::AUTH_MODULE);

        $this->hardDeletePlanetRow($moon, PlanetTypesEnumerator::MOON);

        DB::table('users')
            ->where('id', $user->id)
            ->where('current_planet', $moon)
            ->update(['current_planet' => DB::raw('home_planet_id')]);

        session()->flash('success', __('admin/users.us_moon_hard_deleted'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    private function dateFormatExtended(): string
    {
        return (string) (Options::getInstance()->get('date_format_extended') ?? 'Y-m-d H:i:s');
    }
}
