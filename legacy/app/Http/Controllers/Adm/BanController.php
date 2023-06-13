<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Adm\Ban;

class BanController extends BaseController
{
    private array $user;
    private int $_users_count = 0;
    private int $_banned_count = 0;
    private Ban $banModel;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

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
        if (isset($_POST['unban_name']) && $_POST['unban_name']) {
            $username = $_POST['unban_name'];

            $this->banModel->unbanUser($username);

            session()->flash('success', (str_replace('%s', $username, __('admin/ban.bn_lift_ban_success'))));
        }

        $parse['users_list'] = $this->getUsersList();
        $parse['banned_list'] = $this->getBannedList();
        $parse['users_amount'] = $this->_users_count;
        $parse['banned_amount'] = $this->_banned_count;

        return $parse;
    }

    private function showBan()
    {
        $parse['reason'] = '';
        $ban_name = isset($_GET['ban_name']) ? $_GET['ban_name'] : null;

        if (isset($_GET['banuser']) && isset($ban_name)) {
            $parse['name'] = $ban_name;
            $parse['banned_until'] = '';
            $parse['changedate'] = __('admin/ban.bn_auto_lift_ban_message');
            $parse['vacation'] = '';

            $banned_user = $this->banModel->getBannedUserData($ban_name);

            if ($banned_user) {
                $parse['banned_until'] = '<tr><th>' . __('admin/ban.bn_banned_until') . '</th><td>' . date(Functions::readConfig('date_format_extended'), (int) $banned_user['banned_longer']) . '</td></tr>';
                $parse['reason'] = $banned_user['banned_theme'];
                $parse['changedate'] = Administration::showPopUp(__('admin/ban.bn_change_date'), __('admin/ban.bn_edit_ban_help'));
                $parse['vacation'] = $banned_user['preference_vacation_mode'] ? 'checked="checked"' : '';
            }

            if (isset($_POST['bannow']) && $_POST['bannow']) {
                if (!is_numeric($_POST['days']) or !is_numeric($_POST['hour'])) {
                    session()->flash('warning', __('admin/ban.bn_all_fields_required'));
                } else {
                    $reas = (string) $_POST['text'];
                    $days = (int) $_POST['days'];
                    $hour = (int) $_POST['hour'];
                    $admin_name = $this->user['user_name'];
                    $admin_mail = $this->user['user_email'];
                    $current_time = time();
                    $ban_time = $days * 86400;
                    $ban_time += $hour * 3600;
                    $vacation_mode = isset($_POST['vacat']) ? $_POST['vacat'] : null;

                    if (isset($banned_user)) {
                        if ($banned_user['banned_longer'] > time()) {
                            $ban_time += ($banned_user['banned_longer'] - time());
                        }
                    }

                    if (($ban_time + $current_time) < time()) {
                        $banned_until = $current_time;
                    } else {
                        $banned_until = $current_time + $ban_time;
                    }

                    $this->banModel->setOrUpdateBan(
                        $banned_user,
                        [
                            'ban_name' => $ban_name,
                            'ban_reason' => $reas,
                            'ban_time' => $current_time,
                            'ban_until' => $banned_until,
                            'ban_author' => $admin_name,
                            'ban_author_email' => $admin_mail,
                        ],
                        $vacation_mode
                    );

                    session()->flash('success', (str_replace('%s', $ban_name, __('admin/ban.bn_ban_success'))));
                }
            }
        } else {
            Functions::redirect('admin.php?page=ban');
        }

        return $parse;
    }

    private function getUsersList()
    {
        $query_order = (isset($_GET['order']) && $_GET['order'] == 'id') ? 'user_id' : 'user_name';
        $where_authlevel = '';
        $where_banned = '';
        $users_list = '';

        if ($this->user['user_authlevel'] != 3) {
            $where_authlevel = "WHERE `user_authlevel` < '" . ($this->user['user_authlevel']) . "'";
        }

        if (isset($_GET['view']) && ($_GET['view'] == 'user_banned')) {
            if ($this->user['user_authlevel'] == 3) {
                $where_banned = "WHERE `user_banned` <> '0'";
            } else {
                $where_banned = "AND `user_banned` <> '1'";
            }
        }

        // get the users according to the filters
        $users_query = $this->banModel->getListOfUsers($where_authlevel, $where_banned, $query_order);

        foreach ($users_query as $user) {
            $status = '';

            if ($user['user_banned'] == 1) {
                $status = __('admin/ban.bn_status');
            }

            $users_list .= '<option value="' . $user['user_name'] . '">' . $user['user_name'] . '&nbsp;&nbsp;(ID:&nbsp;' . $user['user_id'] . ')' . $status . '</option>';

            $this->_users_count++;
        }

        return $users_list;
    }

    private function getBannedList()
    {
        $order = (isset($_GET['order2']) && $_GET['order2'] == 'id') ? 'user_id' : 'user_name';
        $banned_list = '';

        $banned_query = $this->banModel->getBannedUsers($order);

        foreach ($banned_query as $user) {
            $banned_list .= '<option value="' . $user['user_name'] . '">' . $user['user_name'] . '&nbsp;&nbsp;(ID:&nbsp;' . $user['user_id'] . ')</option>';

            $this->_banned_count++;
        }

        return $banned_list;
    }
}
