<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Preferences;
use App\Models\User;

class PreferencesService
{
    public function __construct(
        private Preferences $preferences,
    ) {
    }

    public function preferencesFor(User $user): Preferences
    {
        /** @var Preferences $preferences */
        $preferences = $this->preferences->newQuery()->firstOrCreate(
            ['preference_user_id' => $user->id],
            [
                'preference_spy_probes' => 1,
                'preference_planet_sort' => 0,
                'preference_planet_sort_sequence' => 0,
            ]
        );

        return $preferences;
    }

    public function isNicknameChangeAllowed(Preferences $preferences): bool
    {
        return $this->nextNicknameChangeAt($preferences) < time();
    }

    public function nextNicknameChangeAt(Preferences $preferences): int
    {
        return (int) $preferences->preference_nickname_change + ONE_WEEK;
    }
}
