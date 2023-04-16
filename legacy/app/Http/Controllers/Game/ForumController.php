<?php

namespace Xgp\App\Http\Controllers\Game;

use Xgp\App\Core\BaseController;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class ForumController extends BaseController
{
    public const MODULE_ID = 14;

    public function __construct()
    {
        parent::__construct();

        Users::checkSession();
    }

    public function __invoke(): void
    {
        // Check module access
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        // build the page
        $this->buildPage();
    }

    private function buildPage(): void
    {
        Functions::redirect(Functions::readConfig('forum_url'));
    }
}
