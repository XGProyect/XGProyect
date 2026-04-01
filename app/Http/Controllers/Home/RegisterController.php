<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use App\Http\Requests\RegisterRequest;
use App\Mail\Welcome;
use App\Services\Game\PlanetService;
use App\Services\SessionService;
use App\Services\SettingsService;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Home\Register;

class RegisterController extends BaseController
{
    public function __construct(
        private AuthManager $auth,
        private PlanetService $planetService,
        private SessionService $sessionService,
        private SettingsService $settingsService
    ) {
    }

    public function __invoke(RegisterRequest $request): RedirectResponse
    {
        if ($this->settingsService->getBool('reg_enable') === false) {
            return back()->withErrors([
                'username' => __('home/register.re_disabled'),
            ], 'register')->onlyInput('username');
        }

        // define new planet
        $newPlanetCoords = $this->planetService->calculateNewPlanetPosition();

        // create new user + attach new planet
        $newUser = (new Register())->createNewUser(
            $request,
            $newPlanetCoords
        );

        if ($newUser === null) {
            return back()->withErrors($request);
        }

        // send welcome message
        if ($this->settingsService->getString('reg_welcome_message')) {
            Functions::sendMessage(
                $newUser->id,
                0,
                0,
                5,
                __('home/register.re_welcome_message_from'),
                __('home/register.re_welcome_message_subject'),
                str_replace('%s', $newUser->name, __('home/register.re_welcome_message_content'))
            );
        }

        // send welcome email
        if ($this->settingsService->getString('reg_welcome_email')) {
            Mail::to($newUser->email)->send(new Welcome(
                $newUser->name,
                $request->validated('password')
            ));
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $this->sessionService->setLoginData(
                $this->auth->id(),
                $this->auth->getUser()->getAuthPassword()
            );

            return redirect('game.php?page=overview');
        }

        return back()->withErrors($request);
    }
}
