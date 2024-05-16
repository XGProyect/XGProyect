<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;

class TraderOverviewController extends BaseController
{
    public const MODULE_ID = 5;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        Template::legacyView(
            'trader.overview',
            [
                'color' => '',
                'message' => '',
                'currentMode' => '',
            ]
        );
    }
}
