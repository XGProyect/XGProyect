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
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        $this->serverModel = new Server();

        $this->runAction();

        $this->buildPage();
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
    }

    private function buildPage(): void
    {
        $this->gameConfig = $this->serverModel->readAllConfigs();
        $parse['alert'] = '';

        if (isset($_POST['opt_save']) && $_POST['opt_save'] == '1') {
            // CHECK BEFORE SAVE
            $this->runAction();

            // update all the settings
            $this->serverModel->updateConfigs($this->gameConfig);

            $parse['alert'] = Administration::saveMessage('ok', __('admin/server.se_all_ok_message'));
        }

        $parse['game_name'] = $this->gameConfig['game_name'];
        $parse['game_logo'] = $this->gameConfig['game_logo'];
        $parse['language_settings'] = Functions::getLanguages($this->gameConfig['lang']);
        $parse['game_speed'] = $this->gameConfig['game_speed'] / 2500;
        $parse['fleet_speed'] = $this->gameConfig['fleet_speed'] / 2500;
        $parse['resource_multiplier'] = $this->gameConfig['resource_multiplier'];
        $parse['admin_email'] = $this->gameConfig['admin_email'];
        $parse['forum_url'] = $this->gameConfig['forum_url'];
        $parse['closed'] = $this->gameConfig['game_enable'] == 1 ? " checked = 'checked' " : '';
        $parse['close_reason'] = stripslashes($this->gameConfig['close_reason']);
        $parse['date_time_zone'] = $this->timeZonePicker();
        $parse['date_format'] = $this->gameConfig['date_format'];
        $parse['date_format_extended'] = $this->gameConfig['date_format_extended'];
        $parse['adm_attack'] = $this->gameConfig['adm_attack'] == 1 ? " checked = 'checked' " : '';
        $parse['ships'] = $this->percentagePicker($this->gameConfig['fleet_cdr']);
        $parse['defenses'] = $this->percentagePicker($this->gameConfig['defs_cdr']);
        $parse['noobprot'] = $this->gameConfig['noobprotection'] == 1 ? " checked = 'checked' " : '';
        $parse['noobprot2'] = $this->gameConfig['noobprotectiontime'];
        $parse['noobprot3'] = $this->gameConfig['noobprotectionmulti'];

        Template::getInstance()->view(
            'admin.server',
            $parse
        );
    }

    /**
     * method timeZonePicker
     * param
     * return return the select options
     */
    private function timeZonePicker()
    {
        $utc = new DateTimeZone('UTC');
        $dt = new DateTime('now', $utc);
        $time_zones = '';
        $current_time_zone = $this->serverModel->readConfig('date_time_zone');

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

    /**
     * method formatOffset
     * param
     * return return the format offset
     */
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

    /**
     * Percentage picker
     *
     * @param string $current_percentage Current percentage for the field
     *
     * @return string
     */
    private function percentagePicker($current_percentage)
    {
        $options = '';

        for ($i = 0; $i <= 10; $i++) {
            $selected = '';

            if ($i * 10 == $current_percentage) {
                $selected = ' selected = selected ';
            }

            $options .= '<option value="' . $i * 10 . '"' . $selected . '>' . $i * 10 . '%</option>';
        }

        return $options;
    }
}
