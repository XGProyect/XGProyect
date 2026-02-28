<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BanRequest;
use App\Models\Ban;
use App\Models\User;
use App\Services\AdministrationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Libraries\Users;

class BanController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $adminUser = Users::getInstance()->getUserData();

        $usersQuery = User::query()
            ->select('users.id', 'users.name')
            ->leftJoin('bans', 'bans.user_id', '=', 'users.id')
            ->whereNull('bans.user_id')
            ->orderBy('users.name');

        if ((int) $adminUser['authlevel'] !== 3) {
            $usersQuery->where('users.authlevel', '<', $adminUser['authlevel']);
        }

        $bannedUsers = Ban::query()
            ->select('users.id', 'users.name', 'bans.until')
            ->join('users', 'users.id', '=', 'bans.user_id')
            ->orderBy('users.name')
            ->get();

        return view('admin.ban', [
            'users' => $usersQuery->get(),
            'banned_users' => $bannedUsers,
        ]);
    }

    public function ban(BanRequest $request): View | RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var User $targetUser */
        $targetUser = User::where('name', $request->input('ban_name'))->firstOrFail();
        $existingBan = $this->getBanWithPreferences($targetUser->id);

        if ($request->isMethod('post')) {
            return $this->applyBan($request, $targetUser, $existingBan);
        }

        return view('admin.ban_result', [
            'target_user' => $targetUser,
            'existing_ban' => $existingBan,
        ]);
    }

    public function unban(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $username = $request->input('unban_name');
        $user = User::where('name', $username)->first();

        if ($user) {
            Ban::where('user_id', $user->id)->delete();
            session()->flash('success', __('admin/ban.bn_lift_ban_success', ['user' => $username]));
        } else {
            session()->flash('danger', __('admin/ban.bn_user_not_found'));
        }

        return redirect()->route('admin.ban');
    }

    private function applyBan(BanRequest $request, User $targetUser, ?object $existingBan): RedirectResponse
    {
        $days = (int) $request->input('days', 0);
        $hours = (int) $request->input('hour', 0);
        $adminUser = Users::getInstance()->getUserData();

        $banEndTime = Carbon::now()->addDays($days)->addHours($hours);

        if ($existingBan?->until) {
            $bannedUntil = Carbon::parse($existingBan->until);
            if ($bannedUntil->isFuture()) {
                $banEndTime = $bannedUntil->copy()->addDays($days)->addHours($hours);
            }
        }

        if ($banEndTime->isPast()) {
            $banEndTime = Carbon::now();
        }

        $this->upsertBan(
            $targetUser->id,
            (int) $adminUser['id'],
            (string) $request->input('text', ''),
            $banEndTime,
            $request->filled('vacat'),
        );

        session()->flash('success', __('admin/ban.bn_ban_success', ['user' => $targetUser->name]));

        return redirect()->route('admin.ban.form', ['ban_name' => $targetUser->name]);
    }

    private function getBanWithPreferences(int $userId): ?object
    {
        return DB::table('bans')
            ->select('bans.*', 'preferences.preference_user_id', 'preferences.preference_vacation_mode')
            ->join('preferences', 'preferences.preference_user_id', '=', 'bans.user_id')
            ->where('bans.user_id', $userId)
            ->first();
    }

    private function upsertBan(int $userId, int $adminId, string $details, Carbon $until, bool $vacationMode): void
    {
        DB::transaction(function () use ($userId, $adminId, $details, $until, $vacationMode) {
            Ban::updateOrCreate(
                ['user_id' => $userId],
                [
                    'admin_id' => $adminId,
                    'details' => $details,
                    'until' => $until->toDateTimeString(),
                ]
            );

            DB::table('preferences')
                ->where('preference_user_id', $userId)
                ->update(['preference_vacation_mode' => $vacationMode ? time() : null]);

            DB::table('planets')
                ->where('planet_user_id', $userId)
                ->update([
                    'planet_building_metal_mine_percent' => 0,
                    'planet_building_crystal_mine_percent' => 0,
                    'planet_building_deuterium_sintetizer_percent' => 0,
                    'planet_building_solar_plant_percent' => 0,
                    'planet_building_fusion_reactor_percent' => 0,
                    'planet_ship_solar_satellite_percent' => 0,
                ]);
        });
    }
}
