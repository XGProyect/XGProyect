<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use App\Http\Requests\LoginRequest;
use App\Models\Banned;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends BaseController
{
    public function __construct(private SessionService $sessionService)
    {
    }

    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => $request->validated('username'),
            'password' => $request->validated('password'),
        ];

        if (Auth::attempt($credentials)) {
            /** @var User $authUser */
            $authUser = Auth::getUser();

            // set current planet
            User::where('id', $authUser->id)->update(['current_planet' => DB::raw('`home_planet_id`')]);

            // check suspension status
            $banned = $authUser->banned;

            if ($banned !== null && $banned->banned_longer <= time()) {
                Banned::where('banned_who', $authUser->name)->delete();
            }

            $request->session()->regenerate();

            $this->sessionService->setLoginData(
                $authUser->id,
                $authUser->password
            );

            return redirect()->intended('game.php?page=overview');
        }

        return back()->withErrors([
            'username' => __('home/home.hm_invalid_login'),
        ], 'login')->onlyInput('username');
    }
}
