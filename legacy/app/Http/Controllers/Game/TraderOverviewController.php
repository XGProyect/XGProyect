<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;

class TraderOverviewController extends BaseController
{
    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Trader));

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
