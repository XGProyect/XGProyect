<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Ban;
use App\Models\User;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class BanController extends BaseController
{
    private array $user;
    private int $_users_count = 0;
    private int $_banned_count = 0;

    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->user = Users::getInstance()->getUserData();

        $view = 'admin.ban';
        $parse = $this->showDefault();

        if ((isset($_GET['mode']) ? $_GET['mode'] : '')) {
            $view = 'admin.ban_result';
            $parse = $this->showBan();
        }

        Template::legacyView($view, $parse);
    }

    private function showDefault(): array
    {
        $username = request()->get('unban_name');

        if (!empty($username)) {
            $userId = DB::table('users')->where('name', $username)->value('id');
            if ($userId) {
                Ban::where('user_id', $userId)->delete();
            }

            session()->flash('success', (str_replace('%s', $username, __('admin/ban.bn_lift_ban_success'))));
        }

        $parse['users_list'] = $this->getUsersList();
        $parse['banned_list'] = $this->getBannedList();
        $parse['users_amount'] = $this->_users_count;
        $parse['banned_amount'] = $this->_banned_count;

        return $parse;
    }

    private function showBan(): array
    {
        $parse['reason'] = '';
        $banName = isset($_GET['ban_name']) ? $_GET['ban_name'] : null;

        if (isset($_GET['banuser']) && isset($banName)) {
            $parse['name'] = $banName;
            $parse['banned_until'] = '';
            $parse['changedate'] = __('admin/ban.bn_auto_lift_ban_message');
            $parse['vacation'] = '';

            $bannedUser = $this->getBannedUserData($banName);

            if ($bannedUser) {
                $parse['banned_until'] = '<tr><th>' . __('admin/ban.bn_banned_until') . '</th><td>' . (new DateTime($bannedUser['until']))->format(Options::getInstance()->get('date_format_extended')) . '</td></tr>';
                $parse['reason'] = $bannedUser['details'];
                $parse['changedate'] = $this->administrationService->showPopUp(__('admin/ban.bn_change_date'), __('admin/ban.bn_edit_ban_help'));
                $parse['vacation'] = $bannedUser['preference_vacation_mode'] ? 'checked="checked"' : '';
            }

            if (isset($_POST['bannow']) && $_POST['bannow']) {
                if (!is_numeric($_POST['days']) or !is_numeric($_POST['hour'])) {
                    session()->flash('warning', __('admin/ban.bn_all_fields_required'));
                } else {
                    $userName = (string) $banName;
                    $adminId = (int) $this->user['id'];
                    $details = (string) $_POST['text'];
                    $days = (int) $_POST['days'];
                    $hour = (int) $_POST['hour'];
                    $vacationMode = isset($_POST['vacat']) ? $_POST['vacat'] : null;

                    $currentTime = new DateTime();
                    $banInterval = new DateInterval('P' . $days . 'D');
                    $banInterval->h = $hour;

                    $banEndTime = clone $currentTime;
                    $banEndTime->add($banInterval);

                    if (isset($bannedUser) && isset($bannedUser['until'])) {
                        $bannedUntil = new DateTime($bannedUser['until']);
                        if ($bannedUntil > $currentTime) {
                            $remainingBanInterval = $bannedUntil->diff($currentTime);
                            $banEndTime->add($remainingBanInterval);
                        }
                    }

                    $until = $banEndTime > $currentTime ? $banEndTime : $currentTime;

                    $this->setOrUpdateBan(
                        $bannedUser,
                        [
                            'user_name' => $userName,
                            'admin_id' => $adminId,
                            'details' => $details,
                            'until' => Carbon::createFromTimestamp($until->getTimestamp())->toDateTimeString(),
                        ],
                        $vacationMode
                    );

                    session()->flash('success', (str_replace('%s', $banName, (string) __('admin/ban.bn_ban_success'))));
                }
            }
        } else {
            Functions::redirect('admin/ban');
        }

        return $parse;
    }

    private function getBannedUserData(string $banName): ?array
    {
        $result = DB::table('bans AS b')
            ->select('b.*', 'p.preference_user_id', 'p.preference_vacation_mode')
            ->join('users AS u', function ($join) use ($banName) {
                $join->on('u.id', '=', 'b.user_id')
                    ->where('u.name', '=', $banName);
            })
            ->join('preferences AS p', 'p.preference_user_id', '=', 'u.id')
            ->first();

        return $result ? (array) $result : null;
    }

    private function setOrUpdateBan(?array $bannedUser, array $banData, ?string $vacationMode): void
    {
        DB::transaction(function () use ($bannedUser, $banData, $vacationMode) {
            $userId = User::where('name', $banData['user_name'])->value('id');

            if (isset($bannedUser)) {
                Ban::where('user_id', $userId)->update([
                    'admin_id' => $banData['admin_id'],
                    'details' => $banData['details'],
                    'until' => $banData['until'],
                ]);
            } else {
                Ban::create([
                    'user_id' => $userId,
                    'admin_id' => $banData['admin_id'],
                    'details' => $banData['details'],
                    'until' => $banData['until'],
                ]);
            }

            $vacationTime = isset($vacationMode) && $vacationMode != '' ? time() : null;

            DB::table('preferences AS pr')
                ->join('planets AS p', 'p.planet_user_id', '=', DB::raw($userId))
                ->where('pr.preference_user_id', $userId)
                ->update([
                    'pr.preference_vacation_mode' => $vacationTime,
                    'p.planet_building_metal_mine_percent' => 0,
                    'p.planet_building_crystal_mine_percent' => 0,
                    'p.planet_building_deuterium_sintetizer_percent' => 0,
                    'p.planet_building_solar_plant_percent' => 0,
                    'p.planet_building_fusion_reactor_percent' => 0,
                    'p.planet_ship_solar_satellite_percent' => 0,
                ]);
        });
    }

    private function getUsersList(): string
    {
        $query_order = (isset($_GET['order']) && $_GET['order'] == 'id') ? 'id' : 'name';
        $users_list = '';

        $query = DB::table('users AS u')
            ->select('u.id', 'u.name', 'b.until')
            ->leftJoin('bans AS b', 'b.user_id', '=', 'u.id');

        if ($this->user['authlevel'] != 3) {
            $query->where('u.authlevel', '<', $this->user['authlevel']);
        }

        if (isset($_GET['view']) && $_GET['view'] == 'banned') {
            $query->whereNotNull('b.until');
        }

        $users_query = $query->orderBy($query_order, 'ASC')->get();

        foreach ($users_query as $user) {
            $status = '';

            if ($user->until > 0) {
                $status = (string) __('admin/ban.bn_status');
            }

            $users_list .= '<option value="' . $user->name . '">' . $user->name . ' (ID: ' . $user->id . ')' . $status . '</option>';

            $this->_users_count++;
        }

        return $users_list;
    }

    private function getBannedList(): string
    {
        $order = (isset($_GET['order2']) && $_GET['order2'] == 'id') ? 'id' : 'name';
        $banned_list = '';

        $banned_query = DB::table('bans AS b')
            ->select('u.id', 'u.name')
            ->join('users AS u', 'u.id', '=', 'b.user_id')
            ->orderBy($order, 'ASC')
            ->get();

        foreach ($banned_query as $user) {
            $banned_list .= '<option value="' . $user->name . '">' . $user->name . ' (ID: ' . $user->id . ')</option>';

            $this->_banned_count++;
        }

        return $banned_list;
    }
}
