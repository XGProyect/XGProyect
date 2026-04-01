<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Banned;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Functions;

class BannedController extends BaseController
{
    private int $bannedCount = 0;

    public function __construct(private TimingService $timingService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Banned));

        Template::legacyView(
            'banned.view',
            [
                'bannedPlayers' => $this->buildBannedPlayersList(),
                'bannedTotal' => $this->bannedCount,
            ]
        );
    }

    private function buildBannedPlayersList(): array
    {
        $banned = Banned::orderBy('banned_id')->get();
        $bannedList = [];

        if (!empty($banned)) {
            foreach ($banned as $b) {
                $this->bannedCount++;

                $bannedList[] = [
                    'player' => $b['banned_who'],
                    'reason' => $b['banned_theme'],
                    'since' => $this->timingService->formatExtendedDate($b['banned_time']),
                    'until' => $this->timingService->formatExtendedDate($b['banned_longer']),
                    'by' => UrlHelper::setUrl(
                        'mailto:' . $b['banned_email'],
                        $b['banned_author'],
                        $b['banned_author']
                    ),
                ];
            }
        }

        return $bannedList;
    }
}
