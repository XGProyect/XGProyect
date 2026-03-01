<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\Users\UserInfoRequest;
use App\Http\Requests\Admin\Users\UserSettingsRequest;
use App\Models\Alliance;
use App\Models\User;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users as UsersLibrary;
use Xgp\App\Libraries\Users\Shortcuts;

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

    // ── Private helpers ───────────────────────────────────────────────────────

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
}
