<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\Users\UserPlanetRequest;
use App\Http\Traits\Admin\Users\UserPlanetTrait;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Libraries\PlanetLib;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UserMoonController extends BaseController
{
    use UserPlanetTrait;

    private const AUTH_MODULE = UsersController::class;

    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function showMoons(User $user): View
    {
        return view('admin.users_moons', [
            'user' => $user,
            'moons' => $this->getMoons($user->id),
        ]);
    }

    public function createMoon(User $user): View
    {
        $planets = DB::table('planets')
            ->select('planet_id', 'planet_name', 'planet_galaxy', 'planet_system', 'planet_planet')
            ->where('planet_user_id', $user->id)
            ->where('planet_destroyed', 0)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->orderBy('planet_galaxy')->orderBy('planet_system')->orderBy('planet_planet')
            ->get();

        return view('admin.users_moon_create', [
            'user' => $user,
            'planets' => $planets,
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function storeMoon(Request $request, User $user): RedirectResponse
    {
        $planetId = $request->integer('planet');
        $moonName = trim($request->string('name')->toString());
        $diameter = $request->integer('planet_diameter');
        $tempMin = $request->integer('planet_temp_min');
        $tempMax = $request->integer('planet_temp_max');
        $maxFields = $request->integer('planet_field_max');

        $planet = DB::table('planets')
            ->where('planet_id', $planetId)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->first();

        if (!$planet) {
            session()->flash('error', __('admin/users.us_create_moon_planet_doesnt_exist'));

            return redirect()->route('admin.users.moon.create', $user->id);
        }

        $existingMoon = DB::table('planets')
            ->where('planet_galaxy', $planet->planet_galaxy)
            ->where('planet_system', $planet->planet_system)
            ->where('planet_planet', $planet->planet_planet)
            ->where('planet_type', PlanetTypesEnumerator::MOON)
            ->value('planet_id');

        if ($existingMoon || $planet->planet_destroyed != 0) {
            session()->flash('warning', __('admin/users.us_create_moon_add_errors'));

            return redirect()->route('admin.users.moon.create', $user->id);
        }

        $size = 0;
        $mintemp = 0;
        $maxtemp = 0;
        $errors = 0;

        if (!$request->has('diameter_check')) {
            if ($diameter > 0) {
                $size = $diameter;
            } else {
                $errors++;
                session()->flash('warning', __('admin/users.us_create_moon_only_numbers'));
            }
        }

        if (!$request->has('temp_check')) {
            if ($tempMax !== 0 || $tempMin !== 0) {
                $mintemp = $tempMin;
                $maxtemp = $tempMax;
            } else {
                $errors++;
                session()->flash('warning', __('admin/users.us_create_moon_only_numbers'));
            }
        }

        if ($errors === 0) {
            (new PlanetLib())->setNewMoon(
                (int) $planet->planet_galaxy,
                (int) $planet->planet_system,
                (int) $planet->planet_planet,
                $user->id,
                $moonName,
                0,
                $size,
                $maxFields,
                $mintemp,
                $maxtemp
            );

            session()->flash('success', __('admin/users.us_create_moon_added'));

            return redirect()->route('admin.users.moons', $user->id);
        }

        return redirect()->route('admin.users.moon.create', $user->id);
    }

    public function showMoon(User $user, int $moon): View
    {
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
        $this->savePlanetData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    public function showMoonBuildings(User $user, int $moon): View
    {
        return view('admin.users_planet_buildings', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'buildings' => $this->buildBuildingsList($this->getBuildingsData($moon), PlanetTypesEnumerator::MOON),
        ]);
    }

    public function updateMoonBuildings(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->saveBuildingsData($request, $moon, PlanetTypesEnumerator::MOON);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.buildings', [$user->id, $moon]);
    }

    public function showMoonShips(User $user, int $moon): View
    {
        return view('admin.users_planet_ships', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'ships' => $this->buildShipsList($this->getShipsData($moon)),
        ]);
    }

    public function updateMoonShips(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->saveShipsData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.ships', [$user->id, $moon]);
    }

    public function showMoonDefenses(User $user, int $moon): View
    {
        return view('admin.users_planet_defenses', [
            'user' => $user,
            'planet_id' => $moon,
            'planet_type' => PlanetTypesEnumerator::MOON,
            'defenses' => $this->buildDefensesList($this->getDefensesData($moon), PlanetTypesEnumerator::MOON),
        ]);
    }

    public function updateMoonDefenses(Request $request, User $user, int $moon): RedirectResponse
    {
        $this->saveDefensesData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.defenses', [$user->id, $moon]);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function softDeleteMoon(User $user, int $moon): RedirectResponse
    {
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
        return $this->settings->getString('date_format_extended') ?: 'Y-m-d H:i:s';
    }
}
