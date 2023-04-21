<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;

class EncrypterController extends BaseController
{
    private string $unencrypted = '';
    private string $encrypted = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction(): void
    {
        $unencrypted = filter_input(INPUT_POST, 'unencrypted');

        if ($unencrypted) {
            $this->unencrypted = $unencrypted;
            $this->encrypted = Functions::hash($unencrypted);
        }
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->render(
                'admin.encrypter_view',
                [
                    'unencrypted' => $this->unencrypted ?? '',
                    'encrypted' => $this->encrypted ?? '',
                ]
            )
        );
    }
}
