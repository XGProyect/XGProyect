<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserInfoRequest;
use App\Http\Requests\Admin\UserPlanetRequest;
use App\Http\Requests\Admin\UserPremiumRequest;
use App\Http\Requests\Admin\UserResearchRequest;
use App\Http\Requests\Admin\UserSettingsRequest;
use App\Models\Alliance;
use App\Models\User;
use App\Services\AdministrationService;
use DirectoryIterator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\StatisticsLibrary;
use Xgp\App\Libraries\Users as UsersLibrary;
use Xgp\App\Libraries\Users\Shortcuts;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UsersController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $search = trim($request->string('user')->toString());
        $user = null;

        if ($search !== '') {
            $user = User::query()
                ->where('name', $search)
                ->orWhere('email', $search)
                ->first(['id', 'name', 'authlevel', 'onlinetime']);

            if ($user === null) {
                session()->flash('danger', __('admin/users.us_nothing_found'));
            }
        }

        return view('admin.users', [
            'search' => $search,
            'user' => $user,
        ]);
    }

    public function showInfo(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $this->loadFullUserData($user->id);
        $dateFormat = $this->dateFormatExtended();

        return view('admin.users_information', [
            'user' => $user,
            'data' => $data,
            'planets' => $this->getUserPlanets($user->id),
            'alliances' => Alliance::query()->select('alliance_id', 'alliance_name', 'alliance_tag')->orderBy('alliance_name')->get(),
            'all_users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'register_time' => ($data->register_time == 0) ? '-' : date($dateFormat, (int) $data->register_time),
            'online_status' => $this->onlineStatus((int) $data->onlinetime),
            'user_roles' => $this->buildUserRolesList($user),
            'ban' => $this->loadBan($user->id),
            'shortcuts' => $this->parseShortcuts((string) ($data->fleet_shortcuts ?? '')),
        ]);
    }

    public function updateInfo(UserInfoRequest $request, User $user): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $actorLevel = (int) Auth::user()->authlevel;
        $isSelf = (int) Auth::user()->id === $user->id;
        $newLevel = (int) $validated['authlevel'];

        if ($isSelf || $newLevel > $actorLevel) {
            session()->flash('danger', __('admin/users.us_error_authlevel'));
            return redirect()->route('admin.users.info', $user->id);
        }

        $updateData = [
            'name' => $validated['username'],
            'email' => $validated['email'],
            'authlevel' => $newLevel,
            'home_planet_id' => (int) $validated['home_planet_id'],
            'current_planet' => (int) $validated['current_planet'],
            'ally_id' => (int) $validated['ally_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Functions::hash((string) $validated['password']);
        }

        $user->update($updateData);

        if ((int) Auth::user()->id !== $user->id) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.info', $user->id);
    }

    public function showSettings(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $prefs = DB::table('preferences')->where('preference_user_id', $user->id)->first();
        $dateFormat = $this->dateFormatExtended();

        return view('admin.users_settings', [
            'user' => $user,
            'prefs' => $prefs,
            'planet_sort_options' => $this->planetSortOptions(),
            'planet_sort_sequence_options' => $this->planetSortSequenceOptions(),
            'vacation_until' => ($prefs && $prefs->preference_vacation_mode > 0)
                ? date($dateFormat, (int) $prefs->preference_vacation_mode)
                : null,
        ]);
    }

    public function updateSettings(UserSettingsRequest $request, User $user): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $vacationOn = $request->input('preference_vacations_status') === 'on';
        $deleteOn = $request->input('preference_delete_mode') === 'on';
        $vacationTime = Functions::getDefaultVacationTime();
        $currentPrefs = DB::table('preferences')->where('preference_user_id', $user->id)->first();
        $wasOnVacation = $currentPrefs && $currentPrefs->preference_vacation_mode > 0;

        DB::table('preferences')->where('preference_user_id', $user->id)->update([
            'preference_spy_probes' => (int) $request->input('preference_spy_probes', 0),
            'preference_planet_sort' => (int) $request->input('preference_planet_sort', 0),
            'preference_planet_sort_sequence' => (int) $request->input('preference_planet_sort_sequence', 0),
            'preference_vacation_mode' => $vacationOn ? $vacationTime : null,
            'preference_delete_mode' => $deleteOn ? time() : null,
        ]);

        if ($wasOnVacation && !$vacationOn) {
            DB::table('planets')->where('planet_user_id', $user->id)->update([
                'planet_last_update' => time(),
                'planet_building_metal_mine_percent' => 10,
                'planet_building_crystal_mine_percent' => 10,
                'planet_building_deuterium_sintetizer_percent' => 10,
                'planet_building_solar_plant_percent' => 10,
                'planet_building_fusion_reactor_percent' => 10,
                'planet_ship_solar_satellite_percent' => 10,
            ]);
        } elseif (!$wasOnVacation && $vacationOn) {
            DB::table('planets')->where('planet_user_id', $user->id)->update([
                'planet_building_metal_mine_percent' => 0,
                'planet_building_crystal_mine_percent' => 0,
                'planet_building_deuterium_sintetizer_percent' => 0,
                'planet_building_solar_plant_percent' => 0,
                'planet_building_fusion_reactor_percent' => 0,
                'planet_ship_solar_satellite_percent' => 0,
            ]);
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.settings', $user->id);
    }

    public function showResearch(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $research = DB::table('research')->where('research_user_id', $user->id)->first();

        return view('admin.users_research', [
            'user' => $user,
            'technologies' => $this->buildResearchList((array) ($research ?? [])),
        ]);
    }

    public function updateResearch(UserResearchRequest $request, User $user): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $updates = collect($request->validated())
            ->filter(fn ($v, $k) => str_starts_with((string) $k, 'research_'))
            ->map(fn ($v) => (int) $v)
            ->all();

        if (!empty($updates)) {
            DB::table('research')->where('research_user_id', $user->id)->update($updates);
        }

        (new StatisticsLibrary())->rebuildPoints($user->id, 0, 'research');

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.research', $user->id);
    }

    public function showPremium(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $premium = DB::table('premium')->where('premium_user_id', $user->id)->first();
        $dateFormat = $this->dateFormat();

        return view('admin.users_premium', [
            'user' => $user,
            'dark_matter' => (int) ($premium->premium_dark_matter ?? 0),
            'officers' => $this->buildPremiumList((array) ($premium ?? []), $dateFormat),
        ]);
    }

    public function updatePremium(UserPremiumRequest $request, User $user): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $currentPremium = (array) (DB::table('premium')->where('premium_user_id', $user->id)->first() ?? []);
        $updates = [];

        if ($request->filled('premium_dark_matter')) {
            $updates['premium_dark_matter'] = (int) $request->input('premium_dark_matter');
        }

        foreach ($request->all() as $key => $value) {
            if (str_starts_with((string) $key, 'premium_') && $key !== 'premium_dark_matter') {
                $updates[$key] = match ((int) $value) {
                    1 => 0,
                    2 => time() + (3600 * 24 * 7),
                    3 => time() + (3600 * 24 * 30 * 3),
                    default => $currentPremium[$key] ?? 0,
                };
            }
        }

        if (!empty($updates)) {
            DB::table('premium')->where('premium_user_id', $user->id)->update($updates);
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.premium', $user->id);
    }

    public function showPlanets(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.users_planets', [
            'user' => $user,
            'planets' => $this->getPlanetsWithMoons($user->id),
        ]);
    }

    public function showPlanet(User $user, int $planet): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->savePlanetData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    public function showPlanetBuildings(User $user, int $planet): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.users_planet_buildings', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'buildings' => $this->buildBuildingsList($this->getBuildingsData($planet), PlanetTypesEnumerator::PLANET),
        ]);
    }

    public function updatePlanetBuildings(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->saveBuildingsData($request, $planet, PlanetTypesEnumerator::PLANET);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.buildings', [$user->id, $planet]);
    }

    public function showPlanetShips(User $user, int $planet): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.users_planet_ships', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'ships' => $this->buildShipsList($this->getShipsData($planet)),
        ]);
    }

    public function updatePlanetShips(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->saveShipsData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.ships', [$user->id, $planet]);
    }

    public function showPlanetDefenses(User $user, int $planet): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.users_planet_defenses', [
            'user' => $user,
            'planet_id' => $planet,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'defenses' => $this->buildDefensesList($this->getDefensesData($planet), PlanetTypesEnumerator::PLANET),
        ]);
    }

    public function updatePlanetDefenses(Request $request, User $user, int $planet): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->saveDefensesData($request, $planet);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.planet.defenses', [$user->id, $planet]);
    }

    public function softDeletePlanet(User $user, int $planet): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $t = DB::getTablePrefix();
        $destroyTime = time() + (PLANETS_LIFE_TIME * 3600);

        DB::statement(
            "UPDATE `{$t}planets` AS p
             LEFT JOIN `{$t}planets` AS m ON m.`planet_galaxy` = p.`planet_galaxy`
                AND m.`planet_system` = p.`planet_system`
                AND m.`planet_planet` = p.`planet_planet`
                AND m.`planet_type` = '3'
             JOIN `{$t}users` AS u ON u.`id` = p.`planet_user_id`
             SET p.`planet_destroyed` = ?,
                 m.`planet_destroyed` = ?,
                 u.`current_planet` = u.`home_planet_id`
             WHERE p.`planet_id` = ? AND p.`planet_type` = '1'",
            [$destroyTime, $destroyTime, $planet]
        );

        session()->flash('success', __('admin/users.us_planet_soft_deleted'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    public function hardDeletePlanet(User $user, int $planet): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $t = DB::getTablePrefix();

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
            $this->hardDeletePlanetRow((int) $moonId, PlanetTypesEnumerator::MOON);
        }

        $this->hardDeletePlanetRow($planet, PlanetTypesEnumerator::PLANET);

        DB::table('users')
            ->where('id', $user->id)
            ->where('current_planet', $planet)
            ->update(['current_planet' => DB::raw('home_planet_id')]);

        session()->flash('success', __('admin/users.us_planet_hard_deleted'));

        return redirect()->route('admin.users.planets', $user->id);
    }

    public function showMoons(User $user): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.users_moons', [
            'user' => $user,
            'moons' => $this->getMoons($user->id),
        ]);
    }

    public function showMoon(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->authorization(__CLASS__);

        $this->savePlanetData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    public function showMoonBuildings(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->authorization(__CLASS__);

        $this->saveBuildingsData($request, $moon, PlanetTypesEnumerator::MOON);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.buildings', [$user->id, $moon]);
    }

    public function showMoonShips(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->authorization(__CLASS__);

        $this->saveShipsData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.ships', [$user->id, $moon]);
    }

    public function showMoonDefenses(User $user, int $moon): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->authorization(__CLASS__);

        $this->saveDefensesData($request, $moon);
        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.moon.defenses', [$user->id, $moon]);
    }

    public function softDeleteMoon(User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $t = DB::getTablePrefix();
        $destroyTime = time() + (PLANETS_LIFE_TIME * 3600);

        DB::statement(
            "UPDATE `{$t}planets` AS m
             JOIN `{$t}users` AS u ON u.`id` = m.`planet_user_id`
             SET m.`planet_destroyed` = ?,
                 u.`current_planet` = u.`home_planet_id`
             WHERE m.`planet_id` = ? AND m.`planet_type` = '3'",
            [$destroyTime, $moon]
        );

        session()->flash('success', __('admin/users.us_moon_soft_deleted'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    public function hardDeleteMoon(User $user, int $moon): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->hardDeletePlanetRow($moon, PlanetTypesEnumerator::MOON);

        DB::table('users')
            ->where('id', $user->id)
            ->where('current_planet', $moon)
            ->update(['current_planet' => DB::raw('home_planet_id')]);

        session()->flash('success', __('admin/users.us_moon_hard_deleted'));

        return redirect()->route('admin.users.moons', $user->id);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        if ((int) $user->authlevel === UserRanks::ADMIN) {
            session()->flash('danger', __('admin/users.us_cannot_delete_admin'));
            return redirect()->route('admin.users');
        }

        (new UsersLibrary())->deleteUser($user->id);
        session()->flash('success', __('admin/users.us_user_deleted'));

        return redirect()->route('admin.users');
    }

    private function loadFullUserData(int $userId): object
    {
        $p = DB::getTablePrefix();

        $result = DB::table('users AS u')
            ->selectRaw("{$p}u.*, {$p}pr.*")
            ->join('preferences AS pr', 'pr.preference_user_id', '=', 'u.id')
            ->where('u.id', $userId)
            ->first();

        if ($result === null) {
            abort(404);
        }

        return $result;
    }

    private function loadBan(int $userId): ?object
    {
        return DB::table('bans')->where('user_id', $userId)->first();
    }

    /**
     * @return array<int, object>
     */
    private function getUserPlanets(int $userId): array
    {
        return DB::table('planets')
            ->select('planet_id', 'planet_name', 'planet_galaxy', 'planet_system', 'planet_planet')
            ->where('planet_user_id', $userId)
            ->orderBy('planet_galaxy')->orderBy('planet_system')->orderBy('planet_planet')
            ->get()->all();
    }

    /**
     * @return array<int, object>
     */
    private function getPlanetsWithMoons(int $userId): array
    {
        $t = DB::getTablePrefix();

        return array_map(
            fn ($r) => (object) $r,
            DB::select(
                "SELECT p.planet_id, p.planet_name, p.planet_image, p.planet_galaxy, p.planet_system,
                        p.planet_planet, p.planet_destroyed,
                        m.planet_id AS moon_id, m.planet_name AS moon_name,
                        m.planet_image AS moon_image, m.planet_destroyed AS moon_destroyed
                 FROM `{$t}planets` AS p
                 LEFT JOIN `{$t}planets` AS m
                     ON m.planet_galaxy = p.planet_galaxy
                     AND m.planet_system = p.planet_system
                     AND m.planet_planet = p.planet_planet
                     AND m.planet_type = 3
                 WHERE p.planet_user_id = ? AND p.planet_type = 1
                 ORDER BY p.planet_galaxy, p.planet_system, p.planet_planet",
                [$userId]
            )
        );
    }

    /**
     * @return array<int, object>
     */
    private function getMoons(int $userId): array
    {
        return DB::table('planets')
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::MOON)
            ->orderBy('planet_galaxy')->orderBy('planet_system')->orderBy('planet_planet')
            ->get()->all();
    }

    private function getPlanetData(int $planetId, int $type): ?object
    {
        $t = DB::getTablePrefix();

        $result = DB::select(
            "SELECT p.*, b.*, s.*, d.*
             FROM `{$t}planets` AS p
             INNER JOIN `{$t}buildings` AS b ON b.building_planet_id = p.planet_id
             INNER JOIN `{$t}ships` AS s ON s.ship_planet_id = p.planet_id
             INNER JOIN `{$t}defenses` AS d ON d.defense_planet_id = p.planet_id
             WHERE p.planet_id = ? AND p.planet_type = ?",
            [$planetId, $type]
        );

        return $result ? (object) (array) $result[0] : null;
    }

    private function getBuildingsData(int $planetId): object
    {
        return (object) ((array) (DB::table('buildings')->where('building_planet_id', $planetId)->first() ?? new \stdClass()));
    }

    private function getShipsData(int $planetId): object
    {
        return (object) ((array) (DB::table('ships')->where('ship_planet_id', $planetId)->first() ?? new \stdClass()));
    }

    private function getDefensesData(int $planetId): object
    {
        return (object) ((array) (DB::table('defenses')->where('defense_planet_id', $planetId)->first() ?? new \stdClass()));
    }

    private function savePlanetData(Request $request, int $planetId): void
    {
        $stringFields = ['planet_name', 'planet_image'];
        $skipFields = ['planet_b_building_id', 'planet_b_tech_id', 'planet_b_hangar_id'];
        $updates = [];

        // Explicitly handle the destroyed toggle — defaults to 0 (cancel) if not submitted
        $destroyedValue = (int) $request->input('planet_destroyed', 0);
        $updates['planet_destroyed'] = ($destroyedValue === 1) ? (time() + (PLANETS_LIFE_TIME * 3600)) : 0;

        foreach ($request->except(['_token', '_method', 'planet_destroyed', ...$skipFields]) as $field => $value) {
            if ($value === null) {
                continue; // skip nullable fields not submitted by this form variant (e.g. moon vs planet)
            }
            if (in_array($field, $stringFields, true)) {
                $updates[$field] = (string) $value;
            } elseif (str_starts_with((string) $field, 'planet_')) {
                $updates[$field] = is_numeric($value) ? (int) $value : (string) $value;
            }
        }

        if (!empty($updates)) {
            DB::table('planets')->where('planet_id', $planetId)->update($updates);
        }
    }

    private function saveBuildingsData(Request $request, int $planetId, int $type): void
    {
        $updates = [];
        $totalFields = 0;

        foreach ($request->all() as $field => $value) {
            if (str_starts_with((string) $field, 'building_')) {
                $level = (int) $value;
                $updates[$field] = $level;
                $totalFields += $level;
            }
        }

        if (!empty($updates)) {
            DB::table('buildings')->where('building_planet_id', $planetId)->update($updates);
        }

        $mondbasis = (int) $request->input('building_mondbasis', 0);

        DB::table('planets')->where('planet_id', $planetId)->update([
            'planet_field_current' => $totalFields,
            'planet_field_max' => DB::raw('IF(`planet_type` = 3, 1 + ' . $mondbasis . ' * ' . FIELDS_BY_MOONBASIS_LEVEL . ', `planet_field_max`)'),
        ]);

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id');
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'buildings');
    }

    private function saveShipsData(Request $request, int $planetId): void
    {
        $updates = [];

        foreach ($request->all() as $field => $value) {
            if (str_starts_with((string) $field, 'ship_')) {
                $updates[$field] = (int) $value;
            }
        }

        if (!empty($updates)) {
            DB::table('ships')->where('ship_planet_id', $planetId)->update($updates);
        }

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id');
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'ships');
    }

    private function saveDefensesData(Request $request, int $planetId): void
    {
        $updates = [];

        foreach ($request->all() as $field => $value) {
            if (str_starts_with((string) $field, 'defense_')) {
                $updates[$field] = (int) $value;
            }
        }

        if (!empty($updates)) {
            DB::table('defenses')->where('defense_planet_id', $planetId)->update($updates);
        }

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id');
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'defenses');
    }

    private function hardDeletePlanetRow(int $planetId, int $type): void
    {
        $t = DB::getTablePrefix();
        $alias = $type === PlanetTypesEnumerator::MOON ? 'm' : 'p';

        DB::statement(
            "DELETE {$alias}, b, s, d
             FROM `{$t}planets` AS {$alias}
             INNER JOIN `{$t}buildings` AS b ON b.`building_planet_id` = {$alias}.`planet_id`
             INNER JOIN `{$t}ships` AS s ON s.`ship_planet_id` = {$alias}.`planet_id`
             INNER JOIN `{$t}defenses` AS d ON d.`defense_planet_id` = {$alias}.`planet_id`
             WHERE {$alias}.`planet_id` = ? AND {$alias}.`planet_type` = ?",
            [$planetId, $type]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildUserRolesList(User $user): array
    {
        $actorLevel = (int) Auth::user()->authlevel;
        $targetLevel = (int) $user->authlevel;
        $isSelf = (int) Auth::user()->id === $user->id;

        return array_map(
            fn (int $role) => [
                'role_id' => $role,
                'selected' => $role === $targetLevel,
                'role_name' => __('admin/global.user_level')[$role],
                'disabled' => $isSelf || $role > $actorLevel,
            ],
            [UserRanks::PLAYER, UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN]
        );
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildResearchList(array $row): array
    {
        $list = [];
        $skip = 3;

        foreach ($row as $key => $value) {
            if (!str_starts_with((string) $key, 'research_')) {
                continue;
            }

            if ($skip-- > 0) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key),
                'level' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildPremiumList(array $row, string $dateFormat): array
    {
        $list = [];

        foreach ($row as $key => $value) {
            if (!str_starts_with((string) $key, 'premium_') || in_array($key, ['premium_dark_matter', 'premium_user_id'], true)) {
                continue;
            }

            $labelKey = 'admin/users.us_user_' . $key;
            $label = __($labelKey);

            if ($label === $labelKey) {
                continue;
            }

            $expire = (int) $value;

            $list[] = [
                'field' => $key,
                'label' => (string) $label,
                'expire' => $expire,
                'active' => $expire > 0 && $expire > time(),
                'status_text' => ($expire === 0 || $expire < time())
                    ? (string) __('admin/users.us_user_premium_inactive')
                    : (string) __('admin/users.us_user_premium_active_until') . date($dateFormat, $expire),
            ];
        }

        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildBuildingsList(object $row, int $type): array
    {
        $excludePlanet = ['building_mondbasis', 'building_phalanx', 'building_jump_gate'];
        $excludeMoon = ['building_metal_mine', 'building_crystal_mine', 'building_deuterium_sintetizer', 'building_solar_plant', 'building_fusion_reactor', 'building_nano_factory', 'building_laboratory', 'building_terraformer', 'building_ally_deposit', 'building_missile_silo'];
        $exclude = $type === PlanetTypesEnumerator::MOON ? $excludeMoon : $excludePlanet;

        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!str_starts_with((string) $key, 'building_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }
            if (in_array($key, $exclude, true)) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key),
                'level' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildShipsList(object $row): array
    {
        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!str_starts_with((string) $key, 'ship_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key),
                'amount' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDefensesList(object $row, int $type): array
    {
        $excludeMoon = ['defense_anti-ballistic_missile', 'defense_interplanetary_missile'];
        $exclude = $type === PlanetTypesEnumerator::MOON ? $excludeMoon : [];

        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!str_starts_with((string) $key, 'defense_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }
            if (in_array($key, $exclude, true)) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key),
                'amount' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePlanetViewData(object $planet, string $dateFormat): array
    {
        $data = (array) $planet;

        $data['planet_field_current'] = $this->countOccupiedFields((int) $data['planet_id']);
        $data['planet_last_update_display'] = date($dateFormat, (int) ($data['planet_last_update'] ?? 0));
        $data['is_destroyed'] = ($data['planet_destroyed'] ?? 0) > 0;
        $data['planet_destroyed_at'] = ($data['planet_destroyed'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_destroyed']) : null;
        $data['planet_b_building_display'] = ($data['planet_b_building'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_building']) : '-';
        $data['planet_b_tech_display'] = ($data['planet_b_tech'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_tech']) : '-';
        $data['planet_b_hangar_display'] = ($data['planet_b_hangar'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_hangar']) : '-';
        $data['planet_last_jump_display'] = ($data['planet_last_jump_time'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_last_jump_time']) : '-';
        $data['planet_invisible_start_display'] = ($data['planet_invisible_start_time'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_invisible_start_time']) : '-';

        return $data;
    }

    private function countOccupiedFields(int $planetId): int
    {
        $row = DB::table('buildings')
            ->where('building_planet_id', $planetId)
            ->selectRaw(
                'COALESCE(
                    building_metal_mine + building_crystal_mine + building_deuterium_sintetizer +
                    building_solar_plant + building_fusion_reactor + building_robot_factory +
                    building_nano_factory + building_hangar + building_metal_store +
                    building_crystal_store + building_deuterium_tank + building_laboratory +
                    building_terraformer + building_ally_deposit + building_missile_silo +
                    building_mondbasis + building_phalanx + building_jump_gate,
                0) AS total'
            )
            ->first();

        return $row ? (int) $row->total : 0;
    }

    /**
     * @return array<string, string>
     */
    private function parseShortcuts(string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        try {
            $shortcuts = new Shortcuts($raw);
            $result = [];

            foreach ($shortcuts->getAllAsArray() as $value) {
                $type = match ((int) ($value['pt'] ?? 0)) {
                    1 => (string) __('admin/users.us_planet_shortcut'),
                    2 => (string) __('admin/users.us_debris_shortcut'),
                    3 => (string) __('admin/users.us_moon_shortcut'),
                    default => '',
                };

                $key = $value['g'] . ';' . $value['s'] . ';' . $value['p'] . ';' . $value['pt'];
                $result[$key] = $value['name'] . ' [' . $value['g'] . ':' . $value['s'] . ':' . $value['p'] . '] ' . $type;
            }

            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildProcessQueue(string $rawQueue): string
    {
        if (empty($rawQueue)) {
            return '<option value="">-</option>';
        }

        $html = '';

        foreach (explode(';', $rawQueue) as $item) {
            $parts = explode(',', $item);
            if (count($parts) < 5) {
                continue;
            }

            $ready = ((int) $parts[3] <= time()) ? 'OK' : date('i:s', (int) $parts[3] - time());
            $techName = __('admin/users.tech')[(int) $parts[0]] ?? "#{$parts[0]}";

            $html .= '<option value="' . $parts[0] . '">'
                . $techName . ' (' . $parts[1] . '^) (' . date('i:s', (int) $parts[2]) . ') (' . $ready . ') [' . $parts[4] . ']'
                . '</option>';
        }

        return $html ?: '<option value="">-</option>';
    }

    /**
     * @return array<string, string>
     */
    private function getPlanetImages(): array
    {
        $dir = public_path('assets/upload/skins/xgproyect/planets');
        $images = [];

        if (!is_dir($dir)) {
            return $images;
        }

        foreach (new DirectoryIterator($dir) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.jpg')) {
                $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getFilename()) ?? '';
                $images[$name] = $file->getFilename();
            }
        }

        ksort($images);

        return $images;
    }

    /**
     * @return array<int, string>
     */
    private function percentOptions(): array
    {
        $opts = [];

        for ($i = 0; $i <= 10; $i++) {
            $opts[$i] = ($i * 10) . '%';
        }

        return $opts;
    }

    /**
     * @return array<int, string>
     */
    private function planetSortOptions(): array
    {
        return [
            0 => (string) __('admin/users.us_user_preference_planet_sort_op1'),
            1 => (string) __('admin/users.us_user_preference_planet_sort_op2'),
            2 => (string) __('admin/users.us_user_preference_planet_sort_op3'),
            3 => (string) __('admin/users.us_user_preference_planet_sort_op4'),
            4 => (string) __('admin/users.us_user_preference_planet_sort_op5'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function planetSortSequenceOptions(): array
    {
        return [
            0 => (string) __('admin/users.us_user_preference_planet_sort_sequence_op1'),
            1 => (string) __('admin/users.us_user_preference_planet_sort_sequence_op2'),
        ];
    }

    private function onlineStatus(int $time): string
    {
        if ($time + 600 >= time()) {
            return 'online';
        }

        if ($time + 900 >= time()) {
            return 'away';
        }

        return 'offline';
    }

    private function dateFormatExtended(): string
    {
        return (string) (Options::getInstance()->get('date_format_extended') ?? 'Y-m-d H:i:s');
    }

    private function dateFormat(): string
    {
        return (string) (Options::getInstance()->get('date_format') ?? 'Y-m-d');
    }
}
