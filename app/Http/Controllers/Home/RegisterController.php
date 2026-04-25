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
use App\Models\Planets;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Xgp\App\Core\Enumerators\UserRanksEnumerator;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\PlanetLib;

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

        $newUser = null;
        try {
            DB::beginTransaction();

            $newUser = User::create(array_merge(
                $request->validated(),
                [
                    'name' => $request->validated()['username'],
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'lastip' => $request->ip(),
                    'ip_at_reg' => $request->ip(),
                    'agent' => $request->header('User-Agent'),
                    'current_page' => $request->getRequestUri(),
                    'register_time' => time(),
                    'onlinetime' => time(),
                    'authlevel' => UserRanksEnumerator::PLAYER,
                ]
            ));

            $newUser->preferences()->create();
            $newUser->premium()->create();
            $newUser->research()->create();
            $newUser->stats()->create();

            (new PlanetLib())->setNewPlanet($newPlanetCoords['galaxy'], $newPlanetCoords['system'], $newPlanetCoords['planet'], $newUser->id, '', true);

            User::where('id', $newUser->id)->update([
                'home_planet_id' => Planets::where('planet_user_id', $newUser->id)->value('planet_id'),
                'current_planet' => Planets::where('planet_user_id', $newUser->id)->value('planet_id'),
                'galaxy' => $newPlanetCoords['galaxy'],
                'system' => $newPlanetCoords['system'],
                'planet' => $newPlanetCoords['planet'],
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollback();
        }

        if ($newUser === null) {
            return back()->withErrors([
                'username' => __('home/register.re_create_fail'),
            ], 'register');
        }

        // send welcome message
        if ($this->settingsService->getString('reg_welcome_message')) {
            /** @var string $welcomeContent */
            $welcomeContent = __('home/register.re_welcome_message_content');
            Functions::sendMessage(
                $newUser->id,
                0,
                0,
                5,
                __('home/register.re_welcome_message_from'),
                __('home/register.re_welcome_message_subject'),
                str_replace('%s', $newUser->name, $welcomeContent)
            );
        }

        // send welcome email
        if ($this->settingsService->getString('reg_welcome_email')) {
            Mail::to($newUser->email)->send(new Welcome(
                $newUser->name,
                (string) $request->string('password')
            ));
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $authUser = $this->auth->getUser();
            if ($authUser !== null) {
                $this->sessionService->setLoginData(
                    $this->auth->id(),
                    $authUser->getAuthPassword()
                );
            }

            return redirect('game.php?page=overview');
        }

        return back()->withErrors([
            'username' => __('home/register.re_create_fail'),
        ], 'register');
    }
}
