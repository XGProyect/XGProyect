<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Xgp\App\Core\BaseController;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class EncrypterController extends BaseController
{
    private string $unencrypted = '';
    private string $encrypted = '';

    public function __construct()
    {
        parent::__construct();

        Administration::checkSession();
    }

    public function __invoke(): void
    {
        // check if the user is allowed to access
        if (!Administration::authorization(__CLASS__, (int) $this->user['user_authlevel'])) {
            die(Administration::noAccessMessage($this->langs->line('no_permissions')));
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
        $this->page->displayAdmin(
            $this->template->set(
                'adm/encrypter_view',
                array_merge(
                    $this->langs->language,
                    [
                        'unencrypted' => $this->unencrypted ?? '',
                        'encrypted' => $this->encrypted ?? '',
                    ]
                )
            )
        );
    }
}
