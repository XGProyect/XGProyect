<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Models\Banned;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;

class BannedController extends BaseController
{
    public const MODULE_ID = 22;

    private int $bannedCount = 0;

    public function __invoke(): void
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

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
                    'since' => Timing::formatExtendedDate($b['banned_time']),
                    'until' => Timing::formatExtendedDate($b['banned_longer']),
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
