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
class UserPlanetController extends BaseController
{
    use UserPlanetTrait;

    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function showPlanets(User $user): View
    {
        return view('admin.users_planets', [
            'user' => $user,
            'planets' => $this->getPlanetsWithMoons($user->id),
        ]);
    }

    public function createPlanet(User $user): View
    {
        return view('admin.users_planet_create', [
            'user' => $user,
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function storePlanet(Request $request, User $user): RedirectResponse
    {
        $galaxy = $request->integer('galaxy');
        $system = $request->integer('system');
        $planet = $request->integer('planet');
        $name = trim($request->string('name')->toString());
        $fieldMax = $request->integer('planet_field_max');
        $error = '';
        $errors = 0;

        $planetExists = DB::table('planets')
            ->where('planet_galaxy', $galaxy)
            ->where('planet_system', $system)
            ->where('planet_planet', $planet)
            ->exists();

        if (!$planetExists) {
            if ($galaxy < 1 || $system < 1 || $planet < 1) {
                $error .= (string) __('admin/users.us_create_planet_unavailable_coords');
                $errors++;
            }

            if (
                $galaxy > MAX_GALAXY_IN_WORLD ||
                $system > MAX_SYSTEM_IN_GALAXY ||
                $planet > MAX_PLANET_IN_SYSTEM
            ) {
                $error .= (string) __('admin/users.us_create_planet_wrong_coords');
                $errors++;
            }

            if ($errors === 0) {
                if ($fieldMax <= 0) {
                    $fieldMax = 163;
                }

                if (strlen($name) <= 0) {
                    $name = (string) __('admin/users.us_create_planet_default_name');
                }

                $this->createNewPlanet($galaxy, $system, $planet, $user->id, $fieldMax, $name);

                session()->flash('success', __('admin/users.us_create_planet_added'));

                return redirect()->route('admin.users.planets', $user->id);
            }

            session()->flash('warning', $error);
        } else {
            session()->flash('warning', __('admin/users.us_create_planet_unavailable_coords'));
        }

        return redirect()->route('admin.users.planet.create', $user->id);
    }

    public function showPlanet(User $user, int $planet): View
    {
        $planetData = $this->getPlanetData($planet, PlanetTypesEnumerator::PLANET);
        if (!$planetData) {
            abort(404);
        }

        return view('admin.users_planet_edit', [
            'user' => $user,
            'planet' => $this->preparePlanetViewData($planetData, $this->dateFormatExtended()),
            'all_users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'images' => $this->getPlanetImages(),
            'percent_options' => $this->percentOptions(),
            'queue_options' => $this->buildProcessQueue((string) ($planetData->planet_b_building_id ?? '')),
        ]);
    }

    public function updatePlanet(UserPlanetRequest $request, User $user, int $planet): RedirectResponse
    {
        $this->savePlanetData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    public function showPlanetBuildings(User $user, int $planet): View
    {
        return view('admin.users_planet_buildings', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'buildings' => $this->buildBuildingsList($this->getBuildingsData($planet), PlanetTypesEnumerator::PLANET),
        ]);
    }

    public function updatePlanetBuildings(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->saveBuildingsData($request, $planet, PlanetTypesEnumerator::PLANET);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.buildings', [$user->id, $planet]);
    }

    public function showPlanetShips(User $user, int $planet): View
    {
        return view('admin.users_planet_ships', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'ships' => $this->buildShipsList($this->getShipsData($planet)),
        ]);
    }

    public function updatePlanetShips(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->saveShipsData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.ships', [$user->id, $planet]);
    }

    public function showPlanetDefenses(User $user, int $planet): View
    {
        return view('admin.users_planet_defenses', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'defenses' => $this->buildDefensesList($this->getDefensesData($planet), PlanetTypesEnumerator::PLANET),
        ]);
    }

    public function updatePlanetDefenses(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->saveDefensesData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.defenses', [$user->id, $planet]);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function softDeletePlanet(User $user, int $planet): RedirectResponse
    {
        // Rule: cannot schedule the only remaining planet for destruction
        if ($this->countUserPlanets($user->id) <= 1) {
            session()->flash('danger', __('admin/users.us_cannot_delete_only_planet'));
            return redirect()->route('admin.users.planets', $user->id);
        }

        $destroyTime = time() + (PLANETS_LIFE_TIME * 3600);

        // If this is the user's home planet, reassign home + current to the next available planet first
        if ($user->home_planet_id === $planet) {
            $nextId = $this->resolveNextHomePlanet($user->id, $planet);
            if ($nextId) {
                DB::table('users')->where('id', $user->id)->update([
                    'home_planet_id' => $nextId,
                    'current_planet' => $nextId,
                ]);
            }
        }

        $prefix = DB::getTablePrefix();

        DB::statement(
            "UPDATE `{$prefix}planets` AS p
             LEFT JOIN `{$prefix}planets` AS m ON m.`planet_galaxy` = p.`planet_galaxy`
                AND m.`planet_system` = p.`planet_system`
                AND m.`planet_planet` = p.`planet_planet`
                AND m.`planet_type` = '3'
             SET p.`planet_destroyed` = ?,
                 m.`planet_destroyed` = ?
             WHERE p.`planet_id` = ? AND p.`planet_type` = '1'",
            [$destroyTime, $destroyTime, $planet]
        );

        session()->flash('success', __('admin/users.us_planet_soft_deleted'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function hardDeletePlanet(User $user, int $planet): RedirectResponse
    {
        // Rule: cannot delete the only remaining planet
        if ($this->countUserPlanets($user->id) <= 1) {
            session()->flash('danger', __('admin/users.us_cannot_delete_only_planet'));
            return redirect()->route('admin.users.planets', $user->id);
        }

        // If this is the user's home planet, reassign home + current to the next available planet
        if ($user->home_planet_id === $planet) {
            $nextId = $this->resolveNextHomePlanet($user->id, $planet);
            if ($nextId) {
                DB::table('users')->where('id', $user->id)->update([
                    'home_planet_id' => $nextId,
                    'current_planet' => $nextId,
                ]);
            }
        }

        // If the user is currently on this planet, send them to the home planet
        if ($user->home_planet_id !== $planet) {
            DB::table('users')
                ->where('id', $user->id)
                ->where('current_planet', $planet)
                ->update(['current_planet' => DB::raw('home_planet_id')]);
        }

        $moonId = DB::table('planets AS p')
            ->join('planets AS m', function ($join) {
                $join->on('m.planet_galaxy', '=', 'p.planet_galaxy')
                     ->on('m.planet_system', '=', 'p.planet_system')
                     ->on('m.planet_planet', '=', 'p.planet_planet')
                     ->where('m.planet_type', '=', PlanetTypesEnumerator::MOON);
            })
            ->where('p.planet_id', $planet)
            ->where('p.planet_type', PlanetTypesEnumerator::PLANET)
            ->value('m.planet_id');

        if ($moonId) {
            $this->hardDeletePlanetRow(intval($moonId), PlanetTypesEnumerator::MOON); // @phpstan-ignore argument.type
        }

        $this->hardDeletePlanetRow($planet, PlanetTypesEnumerator::PLANET);

        session()->flash('success', __('admin/users.us_planet_hard_deleted'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    private function dateFormatExtended(): string
    {
        return $this->settings->getString('date_format_extended') ?: 'Y-m-d H:i:s';
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function createNewPlanet(int $galaxy, int $system, int $planet, int $userId, int $fieldMax, string $name): void
    {
        try {
            DB::transaction(function () use ($galaxy, $system, $planet, $userId, $fieldMax, $name) {
                (new PlanetLib())->setNewPlanet($galaxy, $system, $planet, $userId, '', false);

                DB::table('planets')
                    ->where('planet_galaxy', $galaxy)
                    ->where('planet_system', $system)
                    ->where('planet_planet', $planet)
                    ->where('planet_type', 1)
                    ->update([
                        'planet_field_max' => $fieldMax,
                        'planet_name' => $name,
                    ]);
            });
        } catch (\Exception $e) {
            // transaction rolled back automatically
        }
    }
}
