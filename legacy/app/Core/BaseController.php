<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use Exception;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Page;
use Xgp\App\Libraries\Users;

abstract class BaseController
{
    protected ?Users $userLibrary = null;
    protected ?array $user = [];
    protected ?array $planet = [];
    protected Objects $objects;
    protected ?Page $page = null;
    protected ?Template $template = null;

    public function __construct()
    {
        $this->userLibrary = new Users();
        $this->user = $this->userLibrary->getUserData();
        $this->planet = $this->userLibrary->getPlanetData();

        $this->objects = new Objects();
        $this->page = new Page($this->userLibrary);
        $this->template = new Template();
    }
}
