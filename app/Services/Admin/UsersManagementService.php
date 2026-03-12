<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Services\SettingsService;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;
use Xgp\App\Libraries\PlanetLib;
use Xgp\App\Libraries\Users\Shortcuts;

class UsersManagementService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * Create a new game user together with all required companion rows
     * (research, statistics, premium, preferences) and an initial planet.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function createUser(
        string $name,
        string $email,
        int $auth,
        string $pass,
        int $galaxy,
        int $system,
        int $planet,
    ): void {
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
                    'premium_dark_matter' => $this->settings->getInt('registration_dark_matter'),
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
        } catch (Exception) {
            // transaction rolled back automatically
        }
    }

    /**
     * Load the merged users+preferences row for a given user id.
     * Aborts with 404 if the user does not exist.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function loadFullUserData(int $userId): object
    {
        $prefix = DB::getTablePrefix();

        $result = DB::table('users AS u')
            ->selectRaw("{$prefix}u.*, {$prefix}pr.*")
            ->join('preferences AS pr', 'pr.preference_user_id', '=', 'u.id')
            ->where('u.id', $userId)
            ->first();

        if ($result === null) {
            abort(404);
        }

        return $result;
    }

    /**
     * @return array<int, object>
     */
    public function getPlanets(int $userId): array
    {
        return DB::table('planets')
            ->select('planet_id', 'planet_name', 'planet_galaxy', 'planet_system', 'planet_planet')
            ->where('planet_user_id', $userId)
            ->orderBy('planet_galaxy')->orderBy('planet_system')->orderBy('planet_planet')
            ->get()->all();
    }

    public function loadBan(int $userId): ?object
    {
        return DB::table('bans')->where('user_id', $userId)->first();
    }

    /**
     * Parse the raw fleet-shortcuts string into a display map.
     *
     * @return array<string, string>
     */
    public function parseShortcuts(string $raw): array
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
        } catch (Throwable) {
            return [];
        }
    }
}
