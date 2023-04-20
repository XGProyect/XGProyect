<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Xgp\App\Core\BaseController;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Login;

class LoginController extends BaseController
{
    private Login $loginModel;

    public function __construct()
    {
        parent::__construct();

        Administration::checkSession();

        $this->loginModel = new Login();
    }

    public function __invoke(): void
    {
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
    private function runAction()
    {
        $loginData = filter_input_array(INPUT_POST, [
            'inputEmail' => FILTER_VALIDATE_EMAIL,
            'inputPassword' => FILTER_UNSAFE_RAW,
        ]);

        if (!empty($loginData['inputEmail']) && !empty($loginData['inputPassword'])) {
            $login = $this->loginModel->getLoginData($loginData['inputEmail']);

            if ($login) {
                if (password_verify($loginData['inputPassword'], $login['user_password'])
                    && Administration::adminLogin((int) $login['user_id'], $login['user_password'])) {
                    $redirect = filter_input(INPUT_GET, 'redirect', FILTER_UNSAFE_RAW) ?? 'home';

                    if ($redirect == '') {
                        $redirect = 'home';
                    }

                    // Redirect to panel home
                    Functions::redirect(SYSTEM_ROOT . 'admin.php?page=' . $redirect);
                }
            }

            // If login fails
            Functions::redirect(SYSTEM_ROOT . 'admin.php?page=login&error=1');
        }
    }

    private function buildPage(): void
    {
        $this->page->displayAdmin(
            $this->template->set(
                'adm/login_view',
                array_merge(
                    $this->langs->language,
                    [
                        'alert' => $this->getAlert(),
                        'redirect' => filter_input(INPUT_GET, 'redirect', FILTER_UNSAFE_RAW),
                    ]
                )
            ),
            false,
            false,
            false
        );
    }

    /**
     * Get the alert view
     *
     * @return string
     */
    private function getAlert(): string
    {
        $error = filter_input(INPUT_GET, 'error', FILTER_VALIDATE_INT);

        if ($error == 1) {
            return Administration::saveMessage('error', $this->langs->line('lg_error_wrong_data'), false);
        }

        return '';
    }
}
