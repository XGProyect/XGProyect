<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use DateTime;
use DateTimeZone;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Server;

class ServerController extends BaseController
{
    private $gameConfig = [];
    private Server $serverModel;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->serverModel = new Server();

        $this->runAction();

        $this->gameConfig = $this->serverModel->readAllConfigs();

        Template::getInstance()->view(
            'admin.server',
            [
                'game_name' => $this->gameConfig['game_name'],
                'game_logo' => $this->gameConfig['game_logo'],
                'language_settings' => Functions::getLanguages($this->gameConfig['lang']),
                'game_speed' => $this->gameConfig['game_speed'] / 2500,
                'fleet_speed' => $this->gameConfig['fleet_speed'] / 2500,
                'resource_multiplier' => $this->gameConfig['resource_multiplier'],
                'admin_email' => $this->gameConfig['admin_email'],
                'forum_url' => $this->gameConfig['forum_url'],
                'closed' => $this->gameConfig['game_enable'] == 1 ? " checked = 'checked' " : '',
                'close_reason' => stripslashes($this->gameConfig['close_reason']),
                'date_time_zone' => $this->timeZonePicker(),
                'date_format' => $this->gameConfig['date_format'],
                'date_format_extended' => $this->gameConfig['date_format_extended'],
                'adm_attack' => $this->gameConfig['adm_attack'] == 1 ? " checked = 'checked' " : '',
                'ships' => $this->percentagePicker((int) $this->gameConfig['fleet_cdr']),
                'defenses' => $this->percentagePicker((int) $this->gameConfig['defs_cdr']),
                'noobprot' => $this->gameConfig['noobprotection'] == 1 ? " checked = 'checked' " : '',
                'noobprot2' => $this->gameConfig['noobprotectiontime'],
                'noobprot3' => $this->gameConfig['noobprotectionmulti'],
            ]
        );
    }

    private function runAction(): void
    {
        // NAME
        if (isset($_POST['game_logo']) && $_POST['game_logo'] != '') {
            $this->gameConfig['game_logo'] = $_POST['game_logo'];
        }

        // LOGO
        if (isset($_POST['game_name']) && $_POST['game_name'] != '') {
            $this->gameConfig['game_name'] = $_POST['game_name'];
        }

        // LANGUAGE
        if (isset($_POST['language'])) {
            $this->gameConfig['lang'] = $_POST['language'];
        }

        // GENERAL RATE
        if (isset($_POST['game_speed']) && is_numeric($_POST['game_speed'])) {
            $this->gameConfig['game_speed'] = (2500 * $_POST['game_speed']);
        }

        // SPEED OF FLEET

        if (isset($_POST['fleet_speed']) && is_numeric($_POST['fleet_speed'])) {
            $this->gameConfig['fleet_speed'] = (2500 * $_POST['fleet_speed']);
        }

        // SPEED OF PRODUCTION
        if (isset($_POST['resource_multiplier']) && is_numeric($_POST['resource_multiplier'])) {
            $this->gameConfig['resource_multiplier'] = $_POST['resource_multiplier'];
        }

        // ADMIN EMAIL CONTACT
        if (isset($_POST['admin_email']) && $_POST['admin_email'] != '' && Functions::validEmail($_POST['admin_email'])) {
            $this->gameConfig['admin_email'] = $_POST['admin_email'];
        }

        // FORUM LINK
        if (isset($_POST['forum_url']) && $_POST['forum_url'] != '') {
            $this->gameConfig['forum_url'] = UrlHelper::prepUrl($_POST['forum_url']);
        }

        // ACTIVATE SERVER
        if (isset($_POST['closed']) && $_POST['closed'] == 'on') {
            $this->gameConfig['game_enable'] = 1;
        } else {
            $this->gameConfig['game_enable'] = 0;
        }

        // OFF-LINE MESSAGE
        if (isset($_POST['close_reason']) && $_POST['close_reason'] != '') {
            $this->gameConfig['close_reason'] = addslashes($_POST['close_reason']);
        }

        /*
         * DATE AND TIME PARAMETERS
         */
        // SHORT DATE
        if (isset($_POST['date_time_zone']) && $_POST['date_time_zone'] != '') {
            $this->gameConfig['date_time_zone'] = $_POST['date_time_zone'];
        }

        if (isset($_POST['date_format']) && $_POST['date_format'] != '') {
            $this->gameConfig['date_format'] = $_POST['date_format'];
        }

        // EXTENDED DATE
        if (isset($_POST['date_format_extended']) && $_POST['date_format_extended'] != '') {
            $this->gameConfig['date_format_extended'] = $_POST['date_format_extended'];
        }

        /*
         * SEVERAL PARAMETERS
         */

        // PROTECTION
        if (isset($_POST['adm_attack']) && $_POST['adm_attack'] == 'on') {
            $this->gameConfig['adm_attack'] = 1;
        } else {
            $this->gameConfig['adm_attack'] = 0;
        }

        // SHIPS TO DEBRIS
        if (isset($_POST['Fleet_Cdr']) && is_numeric($_POST['Fleet_Cdr'])) {
            if ($_POST['Fleet_Cdr'] < 0) {
                $this->gameConfig['fleet_cdr'] = 0;
            } else {
                $this->gameConfig['fleet_cdr'] = $_POST['Fleet_Cdr'];
            }
        }

        // DEFENSES TO DEBRIS
        if (isset($_POST['Defs_Cdr']) && is_numeric($_POST['Defs_Cdr'])) {
            if ($_POST['Defs_Cdr'] < 0) {
                $this->gameConfig['defs_cdr'] = 0;
            } else {
                $this->gameConfig['defs_cdr'] = $_POST['Defs_Cdr'];
            }
        }

        // PROTECTION FOR NOVICES
        if (isset($_POST['noobprotection']) && $_POST['noobprotection'] == 'on') {
            $this->gameConfig['noobprotection'] = 1;
        } else {
            $this->gameConfig['noobprotection'] = 0;
        }

        // PROTECTION N. POINTS
        if (isset($_POST['noobprotectiontime']) && is_numeric($_POST['noobprotectiontime'])) {
            $this->gameConfig['noobprotectiontime'] = $_POST['noobprotectiontime'];
        }

        // PROTECCION N. LIMIT POINTS
        if (isset($_POST['noobprotectionmulti']) && is_numeric($_POST['noobprotectionmulti'])) {
            $this->gameConfig['noobprotectionmulti'] = $_POST['noobprotectionmulti'];
        }

        if (isset($_POST['opt_save']) && $_POST['opt_save'] == '1') {
            // update all the settings
            $this->serverModel->updateConfigs($this->gameConfig);

            session()->flash('success', __('admin/server.se_all_ok_message'));
        }
    }

    private function timeZonePicker()
    {
        $utc = new DateTimeZone('UTC');
        $dt = new DateTime('now', $utc);
        $time_zones = '';
        $current_time_zone = $this->serverModel->readConfig('date_time_zone');
        $time_zones_data = [];

        // Get the data
        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $current_tz = new DateTimeZone($tz);
            $offset = $current_tz->getOffset($dt);
            $transition = $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());

            foreach ($transition as $element => $data) {
                $time_zones_data[$data['offset']][] = $tz;
            }
        }

        // Sort by key
        ksort($time_zones_data);

        // Build the combo
        foreach ($time_zones_data as $offset => $tz) {
            $time_zones .= '<optgroup label="GMT' . $this->formatOffset($offset) . '">';

            foreach ($tz as $key => $zone) {
                $time_zones .= '<option value="' . $zone . '" ' . ($current_time_zone == $zone ? ' selected' : '') . ' >' . $zone . '</option>';
            }

            $time_zones .= '</optgroup>';
        }

        // Return data
        return $time_zones;
    }

    private function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 && $minutes == 0) {
            $sign = ' ';
        }

        return $sign . str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $minutes, 2, '0');
    }

    private function percentagePicker(int $percentage): string
    {
        $options = '';

        for ($i = 0; $i <= 10; $i++) {
            $selected = '';

            if ($i * 10 == $percentage) {
                $selected = ' selected = selected ';
            }

            $options .= '<option value="' . $i * 10 . '"' . $selected . '>' . $i * 10 . '%</option>';
        }

        return $options;
    }
}
