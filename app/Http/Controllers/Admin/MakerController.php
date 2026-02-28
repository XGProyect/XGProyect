<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Alliance;
use App\Models\User;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\PlanetLib;

class MakerController extends BaseController
{
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

        Template::legacyView(
            'admin.maker',
            array_merge(
                $this->makeUser(),
                $this->makeAlliace(),
                $this->makePlanet(),
                $this->makeMoon(),
            )
        );
    }

    private function makeUser(): array
    {
        $parse = $this->buildLevelCombo();

        if (isset($_POST['add_user']) && $_POST['add_user']) {
            $name = (string) $_POST['name'];
            $pass = (string) $_POST['password'];
            $email = (string) $_POST['email'];
            $galaxy = (int) $_POST['galaxy'];
            $system = (int) $_POST['system'];
            $planet = (int) $_POST['planet'];
            $auth = (int) $_POST['authlevel'];
            $i = 0;
            $error = '';

            $check_user = $this->checkUserName($name);
            $check_email = $this->checkUserEmail($email);
            $check_planet = $this->checkPlanet($galaxy, $system, $planet);

            if (!is_numeric($galaxy) && !is_numeric($system) && !is_numeric($planet)) {
                $error = __('admin/maker.mk_user_only_numbers');
                $i++;
            } elseif ($galaxy > MAX_GALAXY_IN_WORLD or $system > MAX_SYSTEM_IN_GALAXY || $planet > MAX_PLANET_IN_SYSTEM || $galaxy < 1 || $system < 1 || $planet < 1) {
                $error = __('admin/maker.mk_user_wrong_coords');
                $i++;
            }

            if (!$name or !$email or !$galaxy or !$system or !$planet) {
                $error .= __('admin/maker.mk_user_complete_all');
                $i++;
            }

            if (!Functions::validEmail(strip_tags($email))) {
                $error .= __('admin/maker.mk_user_invalid_email');
                $i++;
            }

            if ($check_user) {
                $error .= __('admin/maker.mk_user_existing_name');
                $i++;
            }

            if ($check_email) {
                $error .= __('admin/maker.mk_user_existing_email');
                $i++;
            }

            if ($check_planet['count'] != 0) {
                $error .= __('admin/maker.mk_user_existing_planet');
                $i++;
            }

            if (isset($_POST['password_check']) && $_POST['password_check']) {
                $pass = Functions::generatePassword();
            } else {
                if (strlen($pass) < 4) {
                    $error .= __('admin/maker.mk_user_invalid_password');
                    $i++;
                }
            }

            if ($i == 0) {
                $this->createNewUser($name, $email, $auth, $pass, $galaxy, $system, $planet);

                session()->flash('success', strtr(__('admin/maker.mk_user_added'), ['%s' => $pass]));
            } else {
                session()->flash('warning', '<br>' . $error);
            }
        }

        return $parse;
    }

    private function makeAlliace(): array
    {
        $parse['founders_combo'] = $this->buildAllianceUsersCombo();

        if (isset($_POST['add_alliance']) && $_POST['add_alliance']) {
            $alliance_name = (string) $_POST['name'];
            $alliance_tag = (string) $_POST['tag'];
            $alliance_founder = (int) $_POST['founder'];

            $check_alliance = $this->checkAlliance($alliance_name, $alliance_tag);

            if (!$check_alliance && !empty($alliance_founder) && $alliance_founder > 0) {
                $this->createAlliance($alliance_name, $alliance_tag, $alliance_founder);

                session()->flash('success', __('admin/maker.mk_alliance_added'));
            } else {
                session()->flash('warning', __('admin/maker.mk_alliance_all_fields'));
            }
        }

        return $parse;
    }

    /**
     * Create a new planet
     *
     * @return array
     */
    private function makePlanet(): array
    {
        $parse['users_combo'] = $this->buildUsersCombo();

        if (isset($_POST['add_planet']) && $_POST['add_planet']) {
            $userId = (int) $_POST['user'];
            $galaxy = (int) $_POST['galaxy'];
            $system = (int) $_POST['system'];
            $planet = (int) $_POST['planet'];
            $name = (string) $_POST['name'];
            $field_max = (int) $_POST['planet_field_max'];
            $i = 0;
            $error = '';

            $check_planet = $this->checkPlanet($galaxy, $system, $planet);
            $user_query = $this->checkUserById($userId);

            if ($check_planet['count'] == 0 && $user_query) {
                if ($galaxy < 1 or $system < 1 or $planet < 1 or !is_numeric($galaxy) or !is_numeric($system) or !is_numeric($planet)) {
                    $error .= __('admin/maker.mk_planet_unavailable_coords');
                    $i++;
                }

                if ($galaxy > MAX_GALAXY_IN_WORLD or $system > MAX_SYSTEM_IN_GALAXY or $planet > MAX_PLANET_IN_SYSTEM) {
                    $error .= __('admin/maker.mk_planet_wrong_coords');
                    $i++;
                }

                if ($i == 0) {
                    if ($field_max <= 0 && !is_numeric($field_max)) {
                        $field_max = '163';
                    }

                    if (strlen($name) <= 0) {
                        $name = __('admin/maker.mk_planet_default_name');
                    }

                    $this->createNewPlanet($galaxy, $system, $planet, $userId, $field_max, $name);

                    session()->flash('success', __('admin/maker.mk_planet_added'));
                } else {
                    session()->flash('warning', $error);
                }
            } else {
                session()->flash('warning', __('admin/maker.mk_planet_unavailable_coords'));
            }
        }

        return $parse;
    }

    /**
     * Create a new moon
     *
     * @return array
     */
    private function makeMoon(): array
    {
        $parse['planets_combo'] = $this->buildPlanetCombo();

        if (isset($_POST['add_moon']) && $_POST['add_moon']) {
            $planet_id = (int) $_POST['planet'];
            $moon_name = (string) $_POST['name'];
            $diameter = (int) $_POST['planet_diameter'];
            $temp_min = (int) $_POST['planet_temp_min'];
            $temp_max = (int) $_POST['planet_temp_max'];
            $max_fields = (int) $_POST['planet_field_max'];

            $moon_planet = $this->checkMoon($planet_id);

            if ($moon_planet && is_numeric($planet_id)) {
                if ($moon_planet['id_moon'] == '' && $moon_planet['planet_type'] == PlanetTypesEnumerator::PLANET && $moon_planet['planet_destroyed'] == 0) {
                    $galaxy = (int) $moon_planet['planet_galaxy'];
                    $system = (int) $moon_planet['planet_system'];
                    $planet = (int) $moon_planet['planet_planet'];
                    $owner = (int) $moon_planet['planet_user_id'];

                    $size = 0;
                    $errors = 0;
                    $mintemp = 0;
                    $maxtemp = 0;

                    if (!isset($_POST['diameter_check'])) {
                        if (is_numeric($diameter)) {
                            $size = $diameter;
                        } else {
                            $errors++;
                            session()->flash('warning', __('admin/maker.mk_moon_only_numbers'));
                        }
                    }

                    if (!isset($_POST['temp_check'])) {
                        if (is_numeric($temp_max) && is_numeric($temp_min)) {
                            $mintemp = $temp_min;
                            $maxtemp = $temp_max;
                        } else {
                            $errors++;
                            session()->flash('warning', __('admin/maker.mk_moon_only_numbers'));
                        }
                    }

                    if ($errors == 0) {
                        $this->createNewMoon(
                            $galaxy,
                            $system,
                            $planet,
                            $owner,
                            $moon_name,
                            $size,
                            $max_fields,
                            $mintemp,
                            $maxtemp
                        );

                        session()->flash('success', __('admin/maker.mk_moon_added'));
                    }
                } else {
                    session()->flash('warning', __('admin/maker.mk_moon_add_errors'));
                }
            } else {
                session()->flash('error', __('admin/maker.mk_moon_planet_doesnt_exist'));
            }
        }

        return $parse;
    }

    /**
     * Build the list of users combo
     *
     * @return string
     */
    private function buildUsersCombo(): string
    {
        $combo_rows = '';
        $users = $this->getAllServerUsers();

        foreach ($users as $users_row) {
            if (isset($_GET['user']) && $_GET['user'] > 0) {
                $combo_rows .= '<option value="' . $users_row['id'] . '" ' . ($_GET['user'] == $users_row['id'] ? ' selected' : '') . '>' . $users_row['name'] . '</option>';
            } else {
                $combo_rows .= '<option value="' . $users_row['id'] . '">' . $users_row['name'] . '</option>';
            }
        }

        return $combo_rows;
    }

    /**
     * Build the list of planets combo
     *
     * @return string
     */
    private function buildPlanetCombo(): string
    {
        $combo_rows = '';
        $planets = $this->getAllActivePlanets();

        foreach ($planets as $planets_row) {
            if (isset($_GET['planet']) && $_GET['planet'] > 0) {
                $combo_rows .= '<option value="' . $planets_row['planet_id'] . '" ' . ($_GET['planet'] == $planets_row['planet_id'] ? 'selected' : '') . ' >' . $planets_row['planet_name'] . ' [' . $planets_row['planet_galaxy'] . ':' . $planets_row['planet_system'] . ':' . $planets_row['planet_planet'] . ']' . '</option>';
            } else {
                $combo_rows .= '<option value="' . $planets_row['planet_id'] . '">' . $planets_row['planet_name'] . ' ' . Format::prettyCoords((int) $planets_row['planet_galaxy'], (int) $planets_row['planet_system'], (int) $planets_row['planet_planet']) . '</option>';
            }
        }

        return $combo_rows;
    }

    /**
     * Build the list of levels combo
     *
     * @return array
     */
    private function buildLevelCombo(): array
    {
        $user_levels = [];
        $ranks = [
            UserRanks::PLAYER,
            UserRanks::GO,
            UserRanks::SGO,
            UserRanks::ADMIN,
        ];

        foreach ($ranks as $rank_id) {
            $user_levels[] = [
                'id' => $rank_id,
                'name' => __('admin/global.user_level')[$rank_id],
            ];
        }

        return [
            'user_levels' => $user_levels,
        ];
    }

    /**
     * Build the list of alliances combo
     *
     * @return string
     */
    private function buildAllianceUsersCombo(): string
    {
        $combo_rows = '';
        $users = $this->getUsersWithoutAlliance();

        foreach ($users as $users_row) {
            $combo_rows .= '<option value="' . $users_row['id'] . '">' . $users_row['name'] . '</option>';
        }

        return $combo_rows;
    }

    //#####################################
    //
    // query helper methods
    //
    //#####################################

    private function checkUserName(string $name): bool
    {
        return User::where('name', $name)->exists();
    }

    private function checkUserEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    private function checkPlanet(int $galaxy, int $system, int $planet): array
    {
        return [
            'count' => DB::table('planets')
                ->where('planet_galaxy', $galaxy)
                ->where('planet_system', $system)
                ->where('planet_planet', $planet)
                ->count(),
        ];
    }

    private function getAllServerUsers(): array
    {
        return User::select('id', 'name')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->toArray();
    }

    private function getUsersWithoutAlliance(): array
    {
        return User::where('ally_id', 0)
            ->where('ally_request', 0)
            ->select('id', 'name')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->toArray();
    }

    private function getAllActivePlanets(): array
    {
        return DB::table('planets')
            ->select('planet_id', 'planet_name', 'planet_galaxy', 'planet_system', 'planet_planet')
            ->where('planet_destroyed', 0)
            ->where('planet_type', 1)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    private function checkUserById(int $userId): ?User
    {
        return User::find($userId);
    }

    private function createNewUser(string $name, string $email, int $auth, string $pass, int $galaxy, int $system, int $planet): void
    {
        try {
            DB::transaction(function () use ($name, $email, $auth, $pass, $galaxy, $system, $planet) {
                $time = time();

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

    private function checkAlliance(string $allianceName, string $allianceTag): bool
    {
        return Alliance::where('alliance_name', $allianceName)
            ->orWhere('alliance_tag', $allianceTag)
            ->exists();
    }

    private function createAlliance(string $allianceName, string $allianceTag, int $allianceFounder): void
    {
        try {
            DB::transaction(function () use ($allianceName, $allianceTag, $allianceFounder) {
                $time = time();
                $rightsString = '[{"rank":"' . __('admin/maker.mk_alliance_founder_rank') . '","rights":{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1}},{"rank":"Newcomer","rights":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0}}]';

                $allianceId = DB::table('alliance')->insertGetId([
                    'alliance_name' => $allianceName,
                    'alliance_tag' => $allianceTag,
                    'alliance_owner' => $allianceFounder,
                    'alliance_register_time' => $time,
                    'alliance_ranks' => $rightsString,
                ]);

                DB::table('alliance_statistics')->insert(['alliance_statistic_alliance_id' => $allianceId]);

                User::where('id', $allianceFounder)->update([
                    'ally_id' => $allianceId,
                    'ally_register_time' => $time,
                ]);
            });
        } catch (\Exception $e) {
            // transaction rolled back automatically
        }
    }

    private function createNewPlanet(int $galaxy, int $system, int $planet, int $userId, int $fieldMax, string $name): void
    {
        try {
            DB::transaction(function () use ($galaxy, $system, $planet, $userId, $fieldMax, $name) {
                (new PlanetLib())->setNewPlanet($galaxy, $system, $planet, $userId, '', false);

                DB::table('planets')
                    ->where('planet_galaxy', $galaxy)
                    ->where('planet_system', $system)
                    ->where('planet_planet', $planet)
                    ->where('planet_type', 1)
                    ->update([
                        'planet_field_max' => $fieldMax,
                        'planet_name' => $name,
                    ]);
            });
        } catch (\Exception $e) {
            // transaction rolled back automatically
        }
    }

    private function checkMoon(int $planetId): array
    {
        $planet = DB::table('planets')
            ->where('planet_id', $planetId)
            ->where('planet_type', 1)
            ->first();

        if (!$planet) {
            return [];
        }

        $moonId = DB::table('planets')
            ->where('planet_galaxy', $planet->planet_galaxy)
            ->where('planet_system', $planet->planet_system)
            ->where('planet_planet', $planet->planet_planet)
            ->where('planet_type', 3)
            ->value('planet_id');

        return array_merge((array) $planet, ['id_moon' => $moonId]);
    }

    private function createNewMoon(int $galaxy, int $system, int $planet, int $owner, string $moonName, int $size, int $maxFields, int $mintemp, int $maxtemp): void
    {
        (new PlanetLib())->setNewMoon($galaxy, $system, $planet, $owner, $moonName, 0, $size, $maxFields, $mintemp, $maxtemp);
    }
}
