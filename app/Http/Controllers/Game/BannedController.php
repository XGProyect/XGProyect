<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Ban;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\Functions;

class BannedController extends BaseController
{
    public function __construct(
        private SettingsService $settings,
        private TimingService $timingService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Banned));

        $bans = Ban::with('user', 'admin')->orderBy('id')->get();

        $bannedPlayers = $bans->map(fn (Ban $ban) => [
            'player' => $ban->user->name,
            'reason' => $ban->details,
            'since'  => $this->timingService->formatExtendedDate($ban->created_at->timestamp),
            'until'  => $ban->until !== null
                ? $this->timingService->formatExtendedDate($ban->until)
                : '∞',
            'by'     => $ban->admin->name,
        ])->all();

        return view('banned.view', [
            'gameTitle'    => $this->settings->getString('game_name'),
            'bannedPlayers' => $bannedPlayers,
            'bannedTotal'   => count($bannedPlayers),
        ]);
    }
}
