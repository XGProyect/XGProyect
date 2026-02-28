<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BanRequest;
use App\Models\Ban;
use App\Models\User;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Users;

class BanController extends BaseController
{
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function index(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $adminUser = Users::getInstance()->getUserData();

        $usersQuery = DB::table('users AS u')
            ->select('u.id', 'u.name')
            ->leftJoin('bans AS b', 'b.user_id', '=', 'u.id')
            ->whereNull('b.user_id')  // exclude already-banned users
            ->orderBy('u.name', 'ASC');

        if ($adminUser['authlevel'] != 3) {
            $usersQuery->where('u.authlevel', '<', $adminUser['authlevel']);
        }

        $users = $usersQuery->get();

        $bannedUsers = DB::table('bans AS b')
            ->select('u.id', 'u.name', 'b.until')
            ->join('users AS u', 'u.id', '=', 'b.user_id')
            ->orderBy('u.name', 'ASC')
            ->get();

        Template::legacyView('admin.ban', [
            'users' => $users,
            'banned_users' => $bannedUsers,
        ]);
    }

    public function ban(BanRequest $request): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $adminUser = Users::getInstance()->getUserData();
        $banName = $request->input('ban_name');

        /** @var User|null $targetUser */
        $targetUser = User::where('name', $banName)->first();

        if (!$targetUser) {
            session()->flash('danger', __('admin/ban.bn_user_not_found'));
            redirect()->route('admin.ban')->send();
            return;
        }

        $existingBan = $this->getBanWithPreferences($targetUser->id);

        if ($request->isMethod('post') && $request->filled('bannow')) {
            $days = (int) $request->input('days', 0);
            $hours = (int) $request->input('hour', 0);
            $details = (string) $request->input('text', '');
            $vacationMode = $request->filled('vacat');

            $banEndTime = Carbon::now()->addDays($days)->addHours($hours);

            // If already banned and the existing ban is in the future, extend from there
            if ($existingBan && $existingBan->until) {
                $bannedUntil = Carbon::parse($existingBan->until);
                if ($bannedUntil->isFuture()) {
                    $banEndTime = $bannedUntil->addDays($days)->addHours($hours);
                }
            }

            // Ensure the ban end time is always in the future
            if ($banEndTime->isPast()) {
                $banEndTime = Carbon::now();
            }

            $this->upsertBan($targetUser->id, (int) $adminUser['id'], $details, $banEndTime, $vacationMode);

            session()->flash('success', __('admin/ban.bn_ban_success', ['user' => $banName]));
            redirect()->route('admin.ban.form', ['ban_name' => $banName])->send();
            return;
        }

        Template::legacyView('admin.ban_result', [
            'target_user' => $targetUser,
            'existing_ban' => $existingBan,
        ]);
    }

    public function unban(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $username = $request->input('unban_name');

        /** @var User|null $user */
        $user = User::where('name', $username)->first();

        if ($user) {
            Ban::where('user_id', $user->id)->delete();
            session()->flash('success', __('admin/ban.bn_lift_ban_success', ['user' => $username]));
        } else {
            session()->flash('danger', __('admin/ban.bn_user_not_found'));
        }

        return redirect()->route('admin.ban');
    }

    private function getBanWithPreferences(int $userId): ?object
    {
        return DB::table('bans AS b')
            ->select('b.*', 'p.preference_user_id', 'p.preference_vacation_mode')
            ->join('preferences AS p', 'p.preference_user_id', '=', 'b.user_id')
            ->where('b.user_id', $userId)
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

            $vacationTime = $vacationMode ? time() : null;

            DB::table('preferences')
                ->where('preference_user_id', $userId)
                ->update(['preference_vacation_mode' => $vacationTime]);

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
