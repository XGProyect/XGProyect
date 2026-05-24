<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Models\Alliance;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class PlayerprofileController extends BaseController
{
    public function __construct(
        private SettingsService $settings,
    ) {
    }

    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Alliance|null $alliance */
        $alliance = $user->ally_id > 0 ? Alliance::query()->find($user->ally_id) : null;

        return view('playerprofile.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'playerName' => $user->name,
            'alliance' => $alliance === null ? null : [
                'id' => $alliance->alliance_id,
                'tag' => strtoupper($alliance->alliance_tag),
            ],
        ]);
    }
}
