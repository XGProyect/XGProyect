<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Models\Sessions;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\StatisticsLibrary;
use Xgp\App\Libraries\Users as UsersLibrary;
use Xgp\App\Libraries\Users\Shortcuts;
use Xgp\App\Models\Adm\Users;

class UsersController extends BaseController
{
    private string $edit = '';
    private int $planet = 0;
    private int $moon = 0;
    private int $id = 0;
    private int $authlevel = 0;
    private $user_query;
    private $stats;
    private Users $usersModel;
    private array $user;
    private UsersLibrary $userLibrary;
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

        $this->stats = new StatisticsLibrary();
        $this->usersModel = new Users();
        $this->user = UsersLibrary::getInstance()->getUserData();
        $this->userLibrary = new UsersLibrary();

        $this->buildPage();
    }

    //#####################################
    //
    // main methods
    //
    //#####################################

    private function buildPage(): void
    {
        $user = isset($_GET['user']) ? trim($_GET['user']) : null;
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $this->edit = isset($_GET['edit']) ? trim($_GET['edit']) : '';
        $this->planet = isset($_GET['planet']) ? trim($_GET['planet']) : 0;
        $this->moon = isset($_GET['moon']) ? trim($_GET['moon']) : 0;

        if ($user != '') {
            $checked_user = $this->usersModel->checkUser($user);

            if (!$checked_user) {
                session()->flash('danger', __('admin/users.us_nothing_found'));
                $user = '';
            } else {
                $this->id = (int) $checked_user['id'];
                $this->authlevel = (int) $checked_user['authlevel'];

                // initial data
                $this->user_query = $this->usersModel->getUserDataById($this->id);

                // save the data
                if (isset($_POST['send_data']) && $_POST['send_data']) {
                    $this->saveData($type);
                }

                // get refreshed data
                $this->user_query = $this->usersModel->getUserDataById($this->id);
            }
        }

        // physical delete
        if (isset($_GET['mode']) && $_GET['mode'] == 'delete' && $this->user_query['authlevel'] != 3) {
            $this->userLibrary->deleteUser($this->user_query['id']);

            session()->flash('success', __('admin/users.us_user_deleted'));
        }

        $parse['type'] = ($type != '') ? $type : 'info';
        $parse['user'] = ($user != '') ? $user : '';
        $parse['status'] = ($user != '') ? '' : ' disabled';
        $parse['status_box'] = ($user != '' && $this->id != $this->user['id']) ? '' : ' disabled';
        $parse['tag'] = ($user != '') ? 'a' : 'button';
        $parse['user_rank'] = __('admin/global.user_level')[$this->authlevel];
        $parse['content'] = ($user != '' && $type != '') ? $this->getData($type) : '';

        Template::legacyView(
            'admin.users',
            $parse
        );
    }

    private function getData(string $type = ''): string
    {
        switch ($type) {
            case 'info':
            case '':
            default:
                return $this->getDataInfo();
                break;
            case 'settings':
                return $this->getDataSettings();
                break;
            case 'research':
                return $this->getDataResearch();
                break;
            case 'premium':
                return $this->getDataPremium();
                break;
            case 'planets':
                return $this->getDataPlanets();
                break;
            case 'moons':
                return $this->getDataMoons();
                break;
        }
    }

    private function saveData(string $type): void
    {
        switch ($type) {
            case 'info':
            case '':
            default:
                $this->saveInfo();
                break;
            case 'settings':
                $this->saveSettings();
                break;
            case 'research':
                $this->saveResearch();
                break;
            case 'premium':
                $this->savePremium();
                break;
            case 'planets':
                switch ($this->edit) {
                    case '':
                    case 'planet':
                    default:
                        $this->savePlanet(1);
                        break;
                    case 'buildings':
                        $this->saveBuildings(1);
                        break;
                    case 'ships':
                        $this->saveShips(1);
                        break;
                    case 'defenses':
                        $this->saveDefenses(1);
                        break;
                }
                break;
            case 'moons':
                switch ($this->edit) {
                    case '':
                    case 'moon':
                    default:
                        $this->savePlanet(3);
                        break;
                    case 'buildings':
                        $this->saveBuildings(3);
                        break;
                    case 'ships':
                        $this->saveShips(3);
                        break;
                    case 'defenses':
                        $this->saveDefenses(3);
                        break;
                }
                break;
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));
    }

    private function deleteData($type): void
    {
        switch ($type) {
            case 'planet':
                //$this->deletePlanet();
                break;

            case 'moon':
                //$this->deleteMoon();
                break;
        }
    }

    private function refreshPage(): void
    {
        $page = (isset($_GET['page']) ? '?page=' . $_GET['page'] : '');
        $type = (isset($_GET['type']) ? '&type=' . $_GET['type'] : '');
        $user = (isset($_GET['user']) ? '&user=' . $_GET['user'] : '');

        Functions::redirect("admin.php{$page}{$type}{$user}");
    }

    //#####################################
    //
    // getData methods
    //
    //#####################################

    private function getDataInfo(): string
    {
        $parse = (array) $this->user_query;
        $parse['information'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_information'));
        $parse['main_planet'] = $this->buildPlanetCombo($this->user_query, 'home_planet_id');
        $parse['current_planet'] = $this->buildPlanetCombo($this->user_query, 'current_planet');
        $parse['alliances'] = $this->buildAllianceCombo($this->user_query);
        $parse['register_time'] = ($this->user_query['register_time'] == 0) ? '-' : date(Options::getInstance()->get('date_format_extended'), (int) $this->user_query['register_time']);
        $parse['onlinetime'] = $this->lastActivity((int) $this->user_query['onlinetime']);
        $parse['user_roles'] = $this->buildUsersRolesList();
        $parse['banned'] = ($this->user_query['until'] === null) ? '<p class="text-error">' . __('admin/global.ge_no') : '<p class="text-success">' . __('admin/global.ge_yes');
        $parse['banned'] .= ($this->user_query['until'] > 0) ? __('admin/users.us_user_information_banned_until') . date(Options::getInstance()->get('date_format'), (int) $this->user_query['until']) . '</p>' : '</p>';
        $parse['fleet_shortcuts'] = $this->buildShortcutsCombo($this->user_query['fleet_shortcuts']);

        return Template::render('admin.users_information', $parse);
    }

    private function getDataSettings(): string
    {
        $parse['settings'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_settings'));
        $parse['preference_planet_sort'] = $this->planetSortCombo();
        $parse['preference_planet_sort_sequence'] = $this->planetOrderCombo();
        $parse['preference_spy_probes'] = $this->user_query['preference_spy_probes'];
        $parse['preference_vacations_status'] = ($this->user_query['preference_vacation_mode'] > 0) ? ' checked="checked" ' : '';
        $parse['preference_vacation_mode'] = ($this->user_query['preference_vacation_mode'] > 0) ? $this->vacationSet() : '';
        $parse['preference_delete_mode'] = ($this->user_query['preference_delete_mode']) ? ' checked="checked" ' : '';

        return Template::render('admin.users_settings', $parse);
    }

    private function getDataResearch(): string
    {
        $parse = (array) $this->user_query;
        $parse['research'] = str_replace(['%s', '%d'], [$this->user_query['name'], $this->id], __('admin/users.us_user_research'));
        $parse['technologies_list'] = $this->researchTable();

        return Template::render('admin.users_research', $parse);
    }

    private function getDataPremium(): string
    {
        $parse['premium'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_premium'));
        $parse['premium_dark_matter'] = $this->user_query['premium_dark_matter'];
        $parse['premium_list'] = $this->premiumTable();

        return Template::render('admin.users_premium', $parse);
    }

    private function getDataPlanets(): string
    {
        $planets_query = $this->usersModel->getAllPlanetsData($this->id, $this->planet, $this->edit);
        $view = '';

        $parse['planets'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_planets'));

        switch (true) {
            case ($this->edit == 'planet' && $planets_query):
                $parse += $this->editMain($planets_query[0]);
                $view = 'admin.users_planets_main';
                break;
            case ($this->edit == 'buildings' && $planets_query):
                $parse['buildings_list'] = $this->editBuildings($planets_query[0], 1);
                $view = 'admin.users_planets_buildings';
                break;
            case ($this->edit == 'ships' && $planets_query):
                $parse['ships_list'] = $this->editShips($planets_query[0]);
                $view = 'admin.users_planets_ships';
                break;
            case ($this->edit == 'defenses' && $planets_query):
                $parse['defenses_list'] = $this->editDefenses($planets_query[0], 1);
                $view = 'admin.users_planets_defenses';
                break;
            case ($this->edit == 'delete'):
                $this->usersModel->softDeletePlanetById($this->planet);
                $this->refreshPage();
                break;
            case '':
            default:
                $parse['planets_list'] = $this->planetsTable($planets_query);
                $view = 'admin.users_planets';
                break;
        }

        return Template::render($view, $parse);
    }

    private function getDataMoons(): string
    {
        $moons_query = $this->usersModel->getAllMoonsData($this->id, $this->moon, $this->edit);
        $view = '';

        $parse['moons'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_moons'));
        $parse['planets'] = str_replace('%s', $this->user_query['name'], __('admin/users.us_user_moons'));

        switch (true) {
            case ($this->edit == 'moon' && $moons_query):
                $parse += $this->editMain($moons_query[0]);
                $view = 'admin.users_moons_main';
                break;
            case ($this->edit == 'buildings' && $moons_query):
                $parse['buildings_list'] = $this->editBuildings($moons_query[0], 3);
                $view = 'admin.users_planets_buildings';
                break;
            case ($this->edit == 'ships' && $moons_query):
                $parse['ships_list'] = $this->editShips($moons_query[0]);
                $view = 'admin.users_planets_ships';
                break;
            case ($this->edit == 'defenses' && $moons_query):
                $parse['defenses_list'] = $this->editDefenses($moons_query[0], 3);
                $view = 'admin.users_planets_defenses';
                break;
            case ($this->edit == 'delete'):
                $this->usersModel->softDeleteMoonById($this->moon);
                $this->refreshPage();
                break;
            case '':
            default:
                $parse['moons_list'] = $this->moonsTable($moons_query);
                $view = 'admin.users_moons';
                break;
        }

        return Template::render($view, $parse);
    }

    //#####################################
    //
    // save / update methods
    //
    //#####################################

    private function saveInfo(): void
    {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $authlevel = isset($_POST['authlevel']) ? $_POST['authlevel'] : -1;
        $id_planet = isset($_POST['id_planet']) ? $_POST['id_planet'] : 0;
        $cur_planet = isset($_POST['current_planet']) ? $_POST['current_planet'] : 0;
        $ally_id = isset($_POST['ally_id']) ? $_POST['ally_id'] : 0;

        $authlevel = (int) $authlevel;
        $id_planet = (int) $id_planet;
        $cur_planet = (int) $cur_planet;
        $ally_id = (int) $ally_id;

        $errors = '';

        if ($username == '' or $this->usersModel->checkUsername($username, $this->id)) {
            $errors .= __('admin/users.us_error_username') . '<br>';
        }

        if ($password != '') {
            $password = "'" . Functions::hash($password) . "'";
        } else {
            $password = '`password`';
        }

        if ($email == '' or $this->usersModel->checkEmail($email, $this->id)) {
            $errors .= __('admin/users.us_error_email') . '<br>';
        }

        if ($authlevel < 0 or $authlevel > 3) {
            $errors .= __('admin/users.us_error_authlevel') . '<br>';
        }

        if ($id_planet <= 0) {
            $errors .= __('admin/users.us_error_idplanet') . '<br>';
        }

        if ($cur_planet <= 0) {
            $errors .= __('admin/users.us_error_current_planet') . '<br>';
        }

        if ($ally_id < 0) {
            $errors .= __('admin/users.us_error_ally_id') . '<br>';
        }

        if ($errors != '') {
            session()->flash('danger', $errors);
        } else {
            $this->usersModel->saveUserData([
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'authlevel' => $authlevel,
                'id_planet' => $id_planet,
                'cur_planet' => $cur_planet,
                'ally_id' => $ally_id,
                'id' => $this->id,
            ]);

            if ($this->user['id'] != $this->id) {
                Sessions::where('user_id', $this->user['id'])->delete();
            }
        }
    }

    private function saveSettings(): void
    {
        $this->usersModel->saveUserPreferences($_POST, $this->id, $this->user_query);
    }

    private function saveResearch(): void
    {
        $this->usersModel->saveTechnologies($_POST, $this->id);
        $this->stats->rebuildPoints($this->id, 0, 'research');
    }

    private function savePremium(): void
    {
        $this->usersModel->savePremium($_POST, $this->id, $this->user_query);
    }

    private function savePlanet(int $type = 1): void
    {
        $id_get = $this->planet;

        if ($type == 3) {
            $id_get = $this->moon;
        }

        if ((int) $id_get <= 0) {
            return;
        }

        $this->usersModel->savePlanet($_POST, $id_get);
    }

    private function saveBuildings(int $type = 1): void
    {
        $id_get = $this->planet;

        if ($type == 3) {
            $id_get = $this->moon;
        }

        $this->usersModel->saveBuildings($_POST, $id_get);
        $this->stats->rebuildPoints($this->id, $id_get, 'buildings');
    }

    private function saveShips(int $type = 1): void
    {
        $id_get = $this->planet;

        if ($type == 3) {
            $id_get = $this->moon;
        }

        $this->usersModel->saveShips($_POST, $id_get);
        $this->stats->rebuildPoints($this->id, $id_get, 'ships');
    }

    private function saveDefenses(int $type = 1): void
    {
        $id_get = $this->planet;

        if ($type == 3) {
            $id_get = $this->moon;
        }

        $this->usersModel->saveDefenses($_POST, $id_get);
        $this->stats->rebuildPoints($this->id, $id_get, 'defenses');
    }

    //#####################################
    //
    // build combo methods
    //
    //#####################################

    private function buildUsersCombo(int $userId): string
    {
        $combo_rows = '';
        $users = $this->usersModel->getAllUsers();

        foreach ($users as $users_row) {
            $combo_rows .= '<option value="' . $users_row['id'] . '" ' . ($users_row['id'] == $userId ? ' selected' : '') . '>' . $users_row['name'] . '</option>';
        }

        return $combo_rows;
    }

    private function buildPlanetCombo(array $user_data, string $id_field): string
    {
        $combo_rows = '';
        $planets = $this->usersModel->getAllPlanetsByUserId($this->id);

        foreach ($planets as $planets_row) {
            if ($user_data[$id_field] == $planets_row['planet_id']) {
                $combo_rows .= '<option value="' . $planets_row['planet_id'] . '" selected>' . $planets_row['planet_name'] . ' [' . $planets_row['planet_galaxy'] . ':' . $planets_row['planet_system'] . ':' . $planets_row['planet_planet'] . ']' . '</option>';
            } else {
                $combo_rows .= '<option value="' . $planets_row['planet_id'] . '">' . $planets_row['planet_name'] . ' [' . $planets_row['planet_galaxy'] . ':' . $planets_row['planet_system'] . ':' . $planets_row['planet_planet'] . ']' . '</option>';
            }
        }

        return $combo_rows;
    }

    private function buildAllianceCombo(array $user_data): string
    {
        $combo_rows = '';
        $alliances = $this->usersModel->getAllAlliances();

        foreach ($alliances as $alliance_row) {
            if ($user_data['ally_id'] == $alliance_row['alliance_id']) {
                $combo_rows .= '<option value="' . $alliance_row['alliance_id'] . '" selected>' . $alliance_row['alliance_name'] . ' [' . $alliance_row['alliance_tag'] . ']' . '</option>';
            } else {
                $combo_rows .= '<option value="' . $alliance_row['alliance_id'] . '">' . $alliance_row['alliance_name'] . ' [' . $alliance_row['alliance_tag'] . ']' . '</option>';
            }
        }

        return $combo_rows;
    }

    private function buildShortcutsCombo($shortcuts): string
    {
        if ($shortcuts) {
            $user_shortcuts = new Shortcuts($shortcuts);

            foreach ($user_shortcuts->getAllAsArray() as $key => $value) {
                $shortcut['description'] = $value['name'] . ' ' . Format::prettyCoords($value['g'], $value['s'], $value['p']) . ' ';

                switch ($value['pt']) {
                    case 1:
                        $shortcut['description'] .= __('admin/users.us_planet_shortcut');
                        break;
                    case 2:
                        $shortcut['description'] .= __('admin/users.us_debris_shortcut');
                        break;
                    case 3:
                        $shortcut['description'] .= __('admin/users.us_moon_shortcut');
                        break;
                    default:
                        $shortcut['description'] .= '';
                        break;
                }

                $shortcut['select'] = 'shortcuts';
                $shortcut['selected'] = '';
                $shortcut['value'] = $value['g'] . ';' . $value['s'] . ';' . $value['p'] . ';' . $value['pt'];
                $shortcut['title'] = $shortcut['description'];
                $shortcuts .= '<option value="' . $shortcut['value'] . '"' . $shortcut['selected'] . '>' . $shortcut['title'] . '</option>';
            }
            return $shortcuts;
        } else {
            return '<option value="">-</option>';
        }
    }

    private function planetSortCombo(): string
    {
        $sort = '';
        $sort_types = [
            0 => __('admin/users.us_user_preference_planet_sort_op1'),
            1 => __('admin/users.us_user_preference_planet_sort_op2'),
            2 => __('admin/users.us_user_preference_planet_sort_op3'),
            3 => __('admin/users.us_user_preference_planet_sort_op4'),
            4 => __('admin/users.us_user_preference_planet_sort_op5'),
        ];

        foreach ($sort_types as $id => $name) {
            $sort .= "<option value =\"{$id}\"" . (($this->user_query['preference_planet_sort'] == $id) ? ' selected' : '') . ">{$name}</option>";
        }

        return $sort;
    }

    private function planetOrderCombo(): string
    {
        $order = '';
        $order_types = [
            0 => __('admin/users.us_user_preference_planet_sort_sequence_op1'),
            1 => __('admin/users.us_user_preference_planet_sort_sequence_op2'),
        ];

        foreach ($order_types as $id => $name) {
            $order .= "<option value =\"{$id}\"" . (($this->user_query['preference_planet_sort_sequence'] == $id) ? ' selected' : '') . ">{$name}</option>";
        }

        return $order;
    }

    private function premiumCombo(): string
    {
        $premium = '';
        $premium_types = [
            0 => '-',
            1 => __('admin/users.us_user_premium_deactivate'),
            2 => __('admin/users.us_user_premium_activate_one_week'),
            3 => __('admin/users.us_user_premium_activate_three_month'),
        ];

        foreach ($premium_types as $id => $name) {
            $premium .= "<option value=\"{$id}\">{$name}</option>";
        }

        return $premium;
    }

    private function buildPercentCombo(int $current_value): string
    {
        $percent = '';
        $percent_values = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        foreach ($percent_values as $id => $number) {
            $percent .= "<option value=\"{$id}\"  " . ($current_value == $number ? ' selected' : '') . '>' . ($number * 10) . '</option>';
        }

        return $percent;
    }

    private function buildProcessQueue($currentQueue): string
    {
        $queueList = '';

        if (!empty($currentQueue)) {
            $currentQueue = explode(';', $currentQueue);

            foreach ($currentQueue as $queues) {
                $queue = explode(',', $queues);

                if ($queue[3] <= time()) {
                    $ready = 'OK';
                } else {
                    $ready = date('i:s', (int) $queue[3] - time());
                }

                $queueList .= "<option value=\"{$queue[0]}\">" . __('admin/users.tech')[$queue[0]] . ' (' . $queue[1] . '^) (' . date('i:s', $queue[2]) . ') (' . $ready . ') [' . $queue[4] . '] </option>';
            }
        }

        return $queueList;
    }

    private function buildImageCombo(string $current_image): string
    {
        $images_dir = opendir(DEFAULT_SKINPATH . 'planets');
        $exceptions = ['.', '..', '.htaccess', 'index.html', '.DS_Store', 'small'];
        $images_options = '';

        while (($image_dir = readdir($images_dir)) !== false) {
            if (strpos($image_dir, '.jpg')) {
                $images_options .= '<option ';

                if ($current_image . '.jpg' == $image_dir) {
                    $images_options .= 'selected = selected';
                }

                $images_options .= ' value="' . preg_replace('/\\.[^.\\s]{3,4}$/', '', $image_dir) . '">' . $image_dir . '</option>';
            }
        }

        return $images_options;
    }

    //#####################################
    //
    // sub tables methods
    //
    //#####################################

    private function researchTable(): array
    {
        $prepare_table = [];
        $flag = 1;

        foreach ($this->user_query as $tech => $level) {
            if (strpos($tech, 'research_') !== false) {
                if ($flag <= 3) { // SKIP NOT REQUIRED FIELDS
                    $flag++;
                } else {
                    $prepare_table[] = [
                        'technology' => __('admin/users.us_user_' . $tech),
                        'field' => $tech,
                        'level' => $level,
                    ];
                }
            }
        }

        return $prepare_table;
    }

    private function premiumTable(): array
    {
        $prepare_table = [];
        $flag = 1;

        foreach ($this->user_query as $officier => $expire) {
            if (strpos($officier, 'premium_') !== false) {
                if ($flag <= 2) { // SKIP NOT REQUIRED FIELDS
                    $flag++;
                } else {
                    if (__('admin/users.us_user_' . $officier) === null) {
                        continue;
                    }

                    $prepare_table[] = [
                        'premium' => __('admin/users.us_user_' . $officier),
                        'status' => ($expire == 0) ? __('admin/users.us_user_premium_inactive') : (__('admin/users.us_user_premium_active_until') . date(Options::getInstance()->get('date_format'), $expire)),
                        'status_style' => ($expire == 0) ? 'text-danger' : 'text-success',
                        'field' => $officier,
                        'combo' => $this->premiumCombo(),
                    ];
                }
            }
        }

        return $prepare_table;
    }

    private function planetsTable(array $planets_data): array
    {
        $imagePath = DEFAULT_SKINPATH . 'planets/small/s_';
        $parse['user'] = $this->user_query['name'];
        $prepare_table = [];

        foreach ($planets_data as $planets) {
            $parse['planet_id'] = $planets['planet_id'];
            $parse['planet_name'] = $planets['planet_name'];
            $parse['planet_image'] = $planets['planet_image'];
            $parse['planet_status'] = '';
            $parse['planet_image_style'] = '';
            $style = '';

            if ($planets['planet_destroyed'] != 0) {
                $parse['planet_status'] = '<strong><a title="' . __('admin/users.us_user_planets_destroyed') . '">
                (' . __('admin/users.us_user_planets_destroyed_short') . ')</a></strong>';
                $parse['planet_image_style'] = 'class="greyout"';
            }

            $parse['moon_id'] = '';
            $parse['moon_name'] = '';
            $parse['moon_image'] = '';
            $parse['moon_status'] = '';

            if (isset($planets['moon_id'])) {
                $parse['moon_id'] = $planets['moon_id'];
                $parse['moon_name'] = str_replace('%s', $planets['moon_name'], __('admin/users.us_user_moon_title'));

                if ($planets['moon_destroyed'] != 0) {
                    $parse['moon_status'] = ' <strong><a title="' . __('admin/users.us_user_planets_destroyed') . '">
                    (' . __('admin/users.us_user_planets_destroyed_short') . ')</a></strong>';
                    $style = 'class="greyout"';
                }

                $parse['moon_image'] = "<img src=\"{$imagePath}{$planets['moon_image']}.jpg\" alt=\"{$planets['moon_image']}.jpg\" title=\"{$planets['moon_image']}.jpg\" border=\"0\" " . $style . '>';
            }

            $prepare_table[] = $parse;
        }

        return $prepare_table;
    }

    private function moonsTable(array $moons_data): array
    {
        $parse['user'] = $this->user_query['name'];
        $prepare_table = [];

        foreach ($moons_data as $moons) {
            $parse['moon_id'] = $moons['planet_id'];
            $parse['moon_name'] = str_replace('%s', $moons['planet_name'], __('admin/users.us_user_moon_title'));
            $parse['moon_image'] = $moons['planet_image'];
            $parse['moon_status'] = '';
            $parse['moon_image_style'] = '';

            if ($moons['planet_destroyed'] != 0) {
                $parse['moon_status'] = '<strong><a title="' . __('admin/users.us_user_planets_destroyed') . '">
                (' . __('admin/users.us_user_planets_destroyed_short') . ')</a></strong>';
                $parse['moon_image_style'] = 'class="greyout"';
            }

            $prepare_table[] = $parse;
        }

        return $prepare_table;
    }

    //#####################################
    //
    // edition methods (pages)
    //
    //#####################################

    private function editMain($planets_data): array
    {
        $parse = $planets_data;
        $parse['planet_user_id'] = $this->buildUsersCombo($parse['planet_user_id']);
        $parse['planet_last_update'] = date(Options::getInstance()->get('date_format_extended'), $parse['planet_last_update']);
        $parse['type1'] = $parse['planet_type'] == PlanetTypesEnumerator::PLANET ? ' selected' : '';
        $parse['type2'] = $parse['planet_type'] == PlanetTypesEnumerator::MOON ? ' selected' : '';
        $parse['dest1'] = $parse['planet_destroyed'] > 0 ? ' selected' : '';
        $parse['dest2'] = $parse['planet_destroyed'] <= 0 ? ' selected' : '';
        $parse['planet_destroyed'] = $parse['planet_destroyed'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_destroyed']) : '-';
        $parse['planet_b_building'] = $parse['planet_b_building'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_b_building']) : '-';
        $parse['planet_b_building_id'] = $this->buildProcessQueue($parse['planet_b_building_id']);
        $parse['planet_b_tech'] = $parse['planet_b_tech'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_b_tech']) : '-';
        $parse['planet_b_hangar'] = $parse['planet_b_hangar'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_b_hangar']) : '-';
        $parse['planet_image'] = $this->buildImageCombo($parse['planet_image']);
        $parse['planet_building_metal_mine_percent'] = $this->buildPercentCombo($parse['planet_building_metal_mine_percent']);
        $parse['planet_building_crystal_mine_percent'] = $this->buildPercentCombo($parse['planet_building_crystal_mine_percent']);
        $parse['planet_building_deuterium_sintetizer_percent'] = $this->buildPercentCombo($parse['planet_building_deuterium_sintetizer_percent']);
        $parse['planet_building_solar_plant_percent'] = $this->buildPercentCombo($parse['planet_building_solar_plant_percent']);
        $parse['planet_building_fusion_reactor_percent'] = $this->buildPercentCombo($parse['planet_building_fusion_reactor_percent']);
        $parse['planet_ship_solar_satellite_percent'] = $this->buildPercentCombo($parse['planet_ship_solar_satellite_percent']);
        $parse['planet_last_jump_time'] = $parse['planet_last_jump_time'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_last_jump_time']) : '-';
        $parse['planet_invisible_start_time'] = $parse['planet_invisible_start_time'] > 0 ? date(Options::getInstance()->get('date_format_extended'), $parse['planet_invisible_start_time']) : '-';

        return $parse;
    }

    private function editBuildings($planets_data, $type = 1): array
    {
        $exclude_buildings = ['building_mondbasis', 'building_phalanx', 'building_jump_gate'];

        if ($type == 3) {
            $exclude_buildings = ['building_metal_mine', 'building_crystal_mine', 'building_deuterium_sintetizer', 'building_solar_plant', 'building_fusion_reactor', 'building_nano_factory', 'building_laboratory', 'building_terraformer', 'building_ally_deposit', 'building_missile_silo'];
        }

        $prepare_table = [];
        $flag = 1;

        foreach ($planets_data as $building => $level) {
            if (strpos($building, 'building_') !== false && !in_array($building, $exclude_buildings)) {
                if ($flag <= 2) { // SKIP NOT REQUIRED FIELDS
                    $flag++;
                } else {
                    $parse['building'] = __('admin/users.us_user_' . $building);
                    $parse['field'] = $building;
                    $parse['level'] = $level;

                    $prepare_table[] = $parse;
                }
            }
        }

        return $prepare_table;
    }

    private function editShips($planets_data): array
    {
        $prepare_table = [];
        $flag = 1;

        foreach ($planets_data as $ship => $amount) {
            if (strpos($ship, 'ship_') !== false) {
                if ($flag <= 2) { // SKIP NOT REQUIRED FIELDS
                    $flag++;
                } else {
                    $parse['ship'] = __('admin/users.us_user_' . $ship);
                    $parse['field'] = $ship;
                    $parse['amount'] = $amount;

                    $prepare_table[] = $parse;
                }
            }
        }

        return $prepare_table;
    }

    private function editDefenses($planets_data, $type = 1): array
    {
        $exclude_buildings = [''];

        if ($type == 3) {
            $exclude_buildings = ['defense_anti-ballistic_missile', 'defense_interplanetary_missile'];
        }

        $prepare_table = [];
        $flag = 1;

        foreach ($planets_data as $defense => $amount) {
            if (strpos($defense, 'defense_') !== false && !in_array($defense, $exclude_buildings)) {
                if ($flag <= 2) { // SKIP NOT REQUIRED FIELDS
                    $flag++;
                } else {
                    $parse['defense'] = __('admin/users.us_user_' . $defense);
                    $parse['field'] = $defense;
                    $parse['amount'] = $amount;

                    $prepare_table[] = $parse;
                }
            }
        }

        return $prepare_table;
    }
    //#####################################
    //
    // edition methods (pages)
    //
    //#####################################

    private function deletePlanet($id_planet = 0): void
    {
        if ($id_planet == 0) {
            $id_planet = $this->planet;
        }

        $this->deleteMoon();

        $this->usersModel->deletePlanetById($id_planet);
    }

    private function deleteMoon($id_moon = 0): void
    {
        if ($id_moon == 0) {
            $id_moon = $this->moon;
        }

        $this->usersModel->deleteMoonById($id_moon);
    }

    //#####################################
    //
    // other required methods
    //
    //#####################################

    private function lastActivity(int $time): string
    {
        if ($time + 60 * 10 >= time()) {
            return '<p class="text-success">' . __('admin/users.us_online') . '</p>';
        }

        if ($time + 60 * 15 >= time()) {
            return '<p class="text-warning">' . __('admin/users.us_minutes') . '</p>';
        }

        return '<p class="text-danger">' . __('admin/users.us_offline') . '</p>';
    }

    private function buildUsersRolesList(): array
    {
        $roles_list = [];
        $roles = [
            UserRanks::PLAYER,
            UserRanks::GO,
            UserRanks::SGO,
            UserRanks::ADMIN,
        ];

        foreach ($roles as $role) {
            $roles_list[] = [
                'role_id' => $role,
                'role_sel' => ($role == $this->user_query['authlevel'] ? 'selected' : ''),
                'role_name' => __('admin/global.user_level')[$role],
            ];
        }

        return $roles_list;
    }

    private function vacationSet(): string
    {
        return __('admin/users.us_user_preference_vacations_until') . date(Options::getInstance()->get('date_format_extended'), $this->user_query['preference_vacation_mode']);
    }
}
