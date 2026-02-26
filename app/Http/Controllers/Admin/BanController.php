<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Adm\Ban;

class BanController extends BaseController
{
    private array $user;
    private int $_users_count = 0;
    private int $_banned_count = 0;
    private Ban $banModel;

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
        $this->banModel = new Ban();

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
            \App\Models\Ban::where('name', $username)
                ->join('users', 'users.id', '=', 'bans.user_id')
                ->delete();

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

            $bannedUser = $this->banModel->getBannedUserData($banName);

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
                    // involved users
                    $userName = (string) $banName;
                    $adminId = (int) $this->user['id'];

                    // ban data
                    $details = (string) $_POST['text'];
                    $days = (int) $_POST['days'];
                    $hour = (int) $_POST['hour'];
                    $vacationMode = isset($_POST['vacat']) ? $_POST['vacat'] : null;

                    // time calculation
                    $currentTime = new DateTime();
                    $banInterval = new DateInterval('P' . $days . 'D');
                    $banInterval->h = $hour;

                    $banEndTime = clone $currentTime;
                    $banEndTime->add($banInterval);

                    if (isset($bannedUser) && isset($bannedUser['until'])) {
                        $bannedUntil = new DateTime($bannedUser['until']);
                        if ($bannedUntil > $currentTime) {
                            // Extend the ban time if the user is already banned
                            $remainingBanInterval = $bannedUntil->diff($currentTime);
                            $banEndTime->add($remainingBanInterval);
                        }
                    }

                    $until = $banEndTime > $currentTime ? $banEndTime : $currentTime;

                    $this->banModel->setOrUpdateBan(
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

    private function getUsersList(): string
    {
        $query_order = (isset($_GET['order']) && $_GET['order'] == 'id') ? 'id' : 'name';
        $where_authlevel = '';
        $where_banned = '';
        $users_list = '';

        if ($this->user['authlevel'] != 3) {
            $where_authlevel = "WHERE `authlevel` < '" . ($this->user['authlevel']) . "'";
        }

        if (isset($_GET['view']) && ($_GET['view'] == 'banned')) {
            if ($this->user['authlevel'] == 3) {
                $where_banned = 'WHERE `until` <> NULL';
            } else {
                $where_banned = "AND `until` > '0'";
            }
        }

        // get the users according to the filters
        $users_query = $this->banModel->getListOfUsers($where_authlevel, $where_banned, $query_order);

        foreach ($users_query as $user) {
            $status = '';

            if ($user['until'] > 0) {
                $status = (string) __('admin/ban.bn_status');
            }

            $users_list .= '<option value="' . $user['name'] . '">' . $user['name'] . ' (ID: ' . $user['id'] . ')' . $status . '</option>';

            $this->_users_count++;
        }

        return $users_list;
    }

    private function getBannedList(): string
    {
        $order = (isset($_GET['order2']) && $_GET['order2'] == 'id') ? 'id' : 'name';
        $banned_list = '';

        $banned_query = $this->banModel->getBannedUsers($order);

        foreach ($banned_query as $user) {
            $banned_list .= '<option value="' . $user['name'] . '">' . $user['name'] . ' (ID: ' . $user['id'] . ')</option>';

            $this->_banned_count++;
        }

        return $banned_list;
    }
}
