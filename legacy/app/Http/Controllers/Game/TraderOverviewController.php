<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class TraderOverviewController extends BaseController
{
    public const MODULE_ID = 5;

    public function __invoke(): void
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        Template::getInstance()->view(
            'trader.overview',
            [
                'color' => '',
                'message' => '',
                'currentMode' => '',
            ]
        );
    }
}
