<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Planets;
use App\Models\User;
use App\Services\SettingsService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Xgp\App\Core\Enumerators\UserRanksEnumerator;
use Xgp\App\Libraries\PlanetLib;

/**
 * Handles the creation and (in the future) the behaviour of bot accounts.
 *
 * For now it creates bot users, each with a single home planet, to populate
 * the universe initially. The intent is that bots will later play on their
 * own (AI play), so generation lives here separately from the controller to
 * keep room for that upcoming behaviour layer.
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class BotService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * Create bot users, each owning a single randomly placed home planet.
     *
     * @param int $amount     How many bots to create.
     * @param int $galaxyFrom First galaxy in the allowed range.
     * @param int $galaxyTo   Last galaxy in the allowed range.
     *
     * @return int Number of bots actually created.
     */
    public function createBots(int $amount, int $galaxyFrom, int $galaxyTo): int
    {
        $created = 0;
        $attempts = 0;
        $maxAttempts = $amount * 25;

        while ($created < $amount && $attempts < $maxAttempts) {
            $attempts++;

            $slot = $this->findFreeSlot($galaxyFrom, $galaxyTo);

            if ($slot === null) {
                continue;
            }

            if ($this->createBot($slot['galaxy'], $slot['system'], $slot['position'])) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Find a random, unoccupied planet slot within the given galaxy range.
     *
     * @return array{galaxy: int, system: int, position: int}|null
     */
    private function findFreeSlot(int $galaxyFrom, int $galaxyTo): ?array
    {
        $galaxy = random_int($galaxyFrom, $galaxyTo);
        $system = random_int(1, MAX_SYSTEM_IN_GALAXY);
        $position = random_int(1, MAX_PLANET_IN_SYSTEM);

        $taken = Planets::where([
            'planet_galaxy' => $galaxy,
            'planet_system' => $system,
            'planet_planet' => $position,
        ])->exists();

        if ($taken) {
            return null;
        }

        return ['galaxy' => $galaxy, 'system' => $system, 'position' => $position];
    }

    /**
     * Create a single bot user together with its companion rows and home planet.
     */
    private function createBot(int $galaxy, int $system, int $position): bool
    {
        $name = $this->uniqueName();
        $email = $this->uniqueEmail();

        try {
            DB::transaction(function () use ($name, $email, $galaxy, $system, $position): void {
                $time = time();

                $bot = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Str::random(32),
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'ip_at_reg' => '',
                    'register_time' => $time,
                    'onlinetime' => $time,
                    'authlevel' => UserRanksEnumerator::PLAYER,
                ]);

                $bot->preferences()->create();
                $bot->premium()->create([
                    'premium_dark_matter' => $this->settings->getInt('registration_dark_matter'),
                ]);
                $bot->research()->create();
                $bot->stats()->create();

                (new PlanetLib())->setNewPlanet($galaxy, $system, $position, $bot->id, '', true);

                $planetId = (int) DB::getPdo()->lastInsertId();

                User::where('id', $bot->id)->update([
                    'home_planet_id' => $planetId,
                    'current_planet' => $planetId,
                    'galaxy' => $galaxy,
                    'system' => $system,
                    'planet' => $position,
                ]);
            });

            return true;
        } catch (Exception $e) {
            // transaction rolled back automatically; keep trying other slots
            return false;
        }
    }

    private function uniqueName(): string
    {
        do {
            $name = 'Bot_' . Str::upper(Str::random(8));
        } while (User::where('name', $name)->exists());

        return $name;
    }

    private function uniqueEmail(): string
    {
        do {
            $email = 'bot_' . Str::lower(Str::random(12)) . '@bots.local';
        } while (User::where('email', $email)->exists());

        return $email;
    }
}
