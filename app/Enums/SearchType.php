<?php

declare(strict_types=1);

namespace App\Enums;

enum SearchType: string
{
    case PlayerName = 'playerName';
    case AllianceTag = 'allianceTag';
    case PlanetName = 'planetNames';

    /**
     * Slug used by the legacy translation keys
     * (sh_error_no_results_player_name, sh_error_no_results_alliance_tag, …).
     */
    public function langSlug(): string
    {
        return match ($this) {
            self::PlayerName => 'player_name',
            self::AllianceTag => 'alliance_tag',
            self::PlanetName => 'planet_names',
        };
    }
}
