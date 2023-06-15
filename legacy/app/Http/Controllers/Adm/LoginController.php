<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;

class LoginController extends BaseController
{
    public function __invoke(): void
    {
        Administration::checkSession();

        $this->runAction();
        $this->setAlert();

        Template::legacyView(
            'admin.login',
            [
                'redirect' => filter_input(INPUT_GET, 'redirect', FILTER_UNSAFE_RAW),
            ]
        );
    }

    private function runAction(): void
    {
        $loginData = filter_input_array(INPUT_POST, [
            'inputEmail' => FILTER_VALIDATE_EMAIL,
            'inputPassword' => FILTER_UNSAFE_RAW,
        ]);

        if (!empty($loginData['inputEmail']) && !empty($loginData['inputPassword'])) {
            if (Auth::attempt(['email' => $loginData['inputEmail'], 'password' => $loginData['inputPassword']])) {
                /** @var User $authUser */
                $authUser = Auth::getUser();

                if ($authUser->authlevel > 1) {
                    $request = request();

                    $request->session()->regenerate();

                    $request->session([
                        'user_id' => $authUser->id,
                        'user_password' => Hash::make(
                            ($authUser->password . '-' . config('SECRETWORD'))
                        ),
                        'admin_id' => $authUser->id,
                        'admin_password' => Hash::make(
                            ($authUser->password . '-' . config('SECRETWORD'))
                        ),
                    ]);
                    $request->session()->save();

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

    private function setAlert(): void
    {
        $error = filter_input(INPUT_GET, 'error', FILTER_VALIDATE_INT);

        if ($error == 1) {
            session()->flash('danger', __('admin/login.lg_error_wrong_data'));
        }
    }
}
