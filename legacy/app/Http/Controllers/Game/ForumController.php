<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class ForumController extends BaseController
{
    public const MODULE_ID = 14;

    public function __invoke(): void
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        Functions::redirect(Functions::readConfig('forum_url'));
    }
}
