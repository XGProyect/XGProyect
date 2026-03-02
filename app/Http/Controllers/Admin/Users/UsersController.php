<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\Users\UserInfoRequest;
use App\Http\Requests\Admin\Users\UserSettingsRequest;
use App\Models\Alliance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\PlanetLib;
use Xgp\App\Libraries\Users as UsersLibrary;
use Xgp\App\Libraries\Users\Shortcuts;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UsersController extends BaseController
{
    public function index(Request $request): View
    {
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

    public function create(): View
    {
        $userLevels = array_map(fn (int $rank) => [
            'id' => $rank,
            'name' => ((array) __('admin/global.user_level'))[$rank],
        ], [UserRanks::PLAYER, UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN]);

        return view('admin.users_create', [
            'user_levels' => $userLevels,
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function store(Request $request): RedirectResponse
    {
        $name = trim($request->string('name')->toString());
        $pass = trim($request->string('password')->toString());
        $email = trim($request->string('email')->toString());
        $galaxy = $request->integer('galaxy');
        $system = $request->integer('system');
        $planet = $request->integer('planet');
        $auth = $request->integer('authlevel');
        $error = '';
        $errors = 0;

        if (
            $galaxy > MAX_GALAXY_IN_WORLD || // @phpstan-ignore constant.notFound
            $system > MAX_SYSTEM_IN_GALAXY || // @phpstan-ignore constant.notFound
            $planet > MAX_PLANET_IN_SYSTEM || // @phpstan-ignore constant.notFound
            $galaxy < 1 || $system < 1 || $planet < 1
        ) {
            $error = (string) __('admin/users.us_create_wrong_coords'); // @phpstan-ignore cast.string
            $errors++;
        }

        if (!$name || !$email || !$galaxy || !$system || !$planet) {
            $error .= (string) __('admin/users.us_create_complete_all'); // @phpstan-ignore cast.string
            $errors++;
        }

        if (!Functions::validEmail(strip_tags($email))) {
            $error .= (string) __('admin/users.us_create_invalid_email'); // @phpstan-ignore cast.string
            $errors++;
        }

        /** @phpstan-ignore staticMethod.notFound */
        if (User::where('name', $name)->exists()) {
            $error .= (string) __('admin/users.us_create_existing_name'); // @phpstan-ignore cast.string
            $errors++;
        }

        /** @phpstan-ignore staticMethod.notFound */
        if (User::where('email', $email)->exists()) {
            $error .= (string) __('admin/users.us_create_existing_email'); // @phpstan-ignore cast.string
            $errors++;
        }

        $planetExists = DB::table('planets')
            ->where('planet_galaxy', $galaxy)
            ->where('planet_system', $system)
            ->where('planet_planet', $planet)
            ->exists();

        if ($planetExists) {
            $error .= (string) __('admin/users.us_create_existing_planet'); // @phpstan-ignore cast.string
            $errors++;
        }

        if ($request->has('password_check')) {
            $pass = Functions::generatePassword();
        } elseif (strlen($pass) < 4) {
            $error .= (string) __('admin/users.us_create_invalid_password'); // @phpstan-ignore cast.string
            $errors++;
        }

        if ($errors === 0) {
            $this->createNewUser($name, $email, $auth, $pass, $galaxy, $system, $planet);
            session()->flash('success', strtr((string) __('admin/users.us_create_added'), ['%s' => $pass])); // @phpstan-ignore cast.string
        } else {
            session()->flash('warning', '<br>' . $error);
        }

        return redirect()->route('admin.users.create');
    }

    public function showInfo(User $user): View
    {
        $data = $this->loadFullUserData($user->id);
        $dateFormat = $this->dateFormatExtended();

        $registerTime = (int) ($data->register_time ?? 0);
        $onlineTime = (int) ($data->onlinetime ?? 0);

        return view('admin.users_information', [
            'user' => $user,
            'data' => $data,
            'planets' => $this->getUserPlanets($user->id),
            'alliances' => Alliance::query()->select('alliance_id', 'alliance_name', 'alliance_tag')->orderBy('alliance_name')->get(),
            'all_users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'register_time' => ($registerTime === 0) ? '-' : date($dateFormat, $registerTime),
            'online_status' => $this->onlineStatus($onlineTime),
            'user_roles' => $this->buildUserRolesList($user),
            'ban' => $this->loadBan($user->id),
            'shortcuts' => $this->parseShortcuts((string) ($data->fleet_shortcuts ?? '')),
        ]);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function updateInfo(UserInfoRequest $request, User $user): RedirectResponse
    {
        /** @var array{username: string, email: string, authlevel: int, home_planet_id: int, current_planet: int, ally_id: int, password?: string} $validated */
        $validated = $request->validated();

        /** @var User $actor */
        $actor = Auth::user();
        $actorLevel = $actor->authlevel;
        $isSelf = $actor->id === $user->id;
        $newLevel = $validated['authlevel'];

        if ($isSelf || $newLevel > $actorLevel) {
            session()->flash('danger', __('admin/users.us_error_authlevel'));
            return redirect()->route('admin.users.info', $user->id);
        }

        $newAllyId = $validated['ally_id'];
        $allyChanged = (int) $user->ally_id !== $newAllyId;

        $updateData = [
            'name' => $validated['username'],
            'email' => $validated['email'],
            'authlevel' => $newLevel,
            'home_planet_id' => $validated['home_planet_id'],
            'current_planet' => $validated['current_planet'],
            'ally_id' => $newAllyId,
        ];

        if ($allyChanged && $newAllyId > 0) {
            $updateData['ally_rank_id'] = 1;
            $updateData['ally_register_time'] = time();
        }

        if ($allyChanged && $newAllyId === 0) {
            $updateData['ally_rank_id'] = 0;
            $updateData['ally_request'] = 0;
            $updateData['ally_request_text'] = '';
            $updateData['ally_register_time'] = 0;
        }

        if (!empty($validated['password'])) {
            /** @SuppressWarnings(PHPMD.StaticAccess) */
            $updateData['password'] = Functions::hash($validated['password']);
        }

        $user->update($updateData);

        if ($actor->id !== $user->id) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.info', $user->id);
    }

    public function showSettings(User $user): View
    {
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

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function updateSettings(UserSettingsRequest $request, User $user): RedirectResponse
    {
        $vacationOn = $request->input('preference_vacations_status') === 'on';
        $deleteOn = $request->input('preference_delete_mode') === 'on';
        /** @SuppressWarnings(PHPMD.StaticAccess) */
        $vacationTime = Functions::getDefaultVacationTime();
        $currentPrefs = DB::table('preferences')->where('preference_user_id', $user->id)->first();
        $wasOnVacation = $currentPrefs && $currentPrefs->preference_vacation_mode > 0;

        DB::table('preferences')->where('preference_user_id', $user->id)->update([
            'preference_spy_probes' => $request->integer('preference_spy_probes', 0),
            'preference_planet_sort' => $request->integer('preference_planet_sort', 0),
            'preference_planet_sort_sequence' => $request->integer('preference_planet_sort_sequence', 0),
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

    public function destroy(User $user): RedirectResponse
    {
        if ((int) $user->authlevel === UserRanks::ADMIN) {
            session()->flash('danger', __('admin/users.us_cannot_delete_admin'));
            return redirect()->route('admin.users');
        }

        (new UsersLibrary())->deleteUser($user->id);
        session()->flash('success', __('admin/users.us_user_deleted'));

        return redirect()->route('admin.users');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function loadFullUserData(int $userId): object
    {
        $prefix = DB::getTablePrefix();

        $result = DB::table('users AS u')
            ->selectRaw("{$prefix}u.*, {$prefix}pr.*")
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
     * @return array<int, array{role_id: int, selected: bool, role_name: string, disabled: bool}>
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function buildUserRolesList(User $user): array
    {
        /** @var User $actor */
        $actor = Auth::user();
        $actorLevel = $actor->authlevel;
        $targetLevel = $user->authlevel;
        $isSelf = $actor->id === $user->id;

        /** @var array<int, string> $roleNames */
        $roleNames = __('admin/global.user_level');

        return array_map(
            fn (int $role) => [
                'role_id' => $role,
                'selected' => $role === $targetLevel,
                'role_name' => $roleNames[$role],
                'disabled' => $isSelf || $role > $actorLevel,
            ],
            [UserRanks::PLAYER, UserRanks::GO, UserRanks::SGO, UserRanks::ADMIN]
        );
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
                    1 => (string) __('admin/users.us_planet_shortcut'), // @phpstan-ignore cast.string
                    2 => (string) __('admin/users.us_debris_shortcut'), // @phpstan-ignore cast.string
                    3 => (string) __('admin/users.us_moon_shortcut'), // @phpstan-ignore cast.string
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

    /**
     * @return array<int, string>
     */
    private function planetSortOptions(): array
    {
        return [
            0 => (string) __('admin/users.us_user_preference_planet_sort_op1'), // @phpstan-ignore cast.string
            1 => (string) __('admin/users.us_user_preference_planet_sort_op2'), // @phpstan-ignore cast.string
            2 => (string) __('admin/users.us_user_preference_planet_sort_op3'), // @phpstan-ignore cast.string
            3 => (string) __('admin/users.us_user_preference_planet_sort_op4'), // @phpstan-ignore cast.string
            4 => (string) __('admin/users.us_user_preference_planet_sort_op5'), // @phpstan-ignore cast.string
        ];
    }

    /**
     * @return array<int, string>
     */
    private function planetSortSequenceOptions(): array
    {
        return [
            0 => (string) __('admin/users.us_user_preference_planet_sort_sequence_op1'), // @phpstan-ignore cast.string
            1 => (string) __('admin/users.us_user_preference_planet_sort_sequence_op2'), // @phpstan-ignore cast.string
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

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function createNewUser(string $name, string $email, int $auth, string $pass, int $galaxy, int $system, int $planet): void
    {
        try {
            DB::transaction(function () use ($name, $email, $auth, $pass, $galaxy, $system, $planet) {
                $time = time();

                /** @phpstan-ignore staticMethod.notFound */
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'ip_at_reg' => request()->ip() ?? '',
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'register_time' => $time,
                    'onlinetime' => $time,
                    'authlevel' => $auth,
                    'password' => $pass,
                ]);

                $lastUserId = $user->id;

                DB::table('research')->insert(['research_user_id' => $lastUserId]);
                DB::table('users_statistics')->insert(['user_statistic_user_id' => $lastUserId]);
                DB::table('premium')->insert([
                    'premium_user_id' => $lastUserId,
                    'premium_dark_matter' => Options::getInstance()->get('registration_dark_matter'),
                ]);
                DB::table('preferences')->insert(['preference_user_id' => $lastUserId]);

                (new PlanetLib())->setNewPlanet($galaxy, $system, $planet, $lastUserId, '', true);

                $lastPlanetId = (int) DB::getPdo()->lastInsertId();

                /** @phpstan-ignore staticMethod.notFound */
                User::where('id', $lastUserId)->update([
                    'home_planet_id' => $lastPlanetId,
                    'current_planet' => $lastPlanetId,
                    'galaxy' => $galaxy,
                    'system' => $system,
                    'planet' => $planet,
                ]);
            });
        } catch (\Exception $e) {
            // transaction rolled back automatically
        }
    }
}
