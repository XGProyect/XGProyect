<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use App\Http\Requests\LoginRequest;
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
            if ($authUser->ban !== null && $authUser->ban->until <= time()) {
                $authUser->ban->delete();
            }

            $request->session()->regenerate();

            $this->sessionService->setLoginData(
                $authUser->id,
                $authUser->password
            );

            return redirect('game.php?page=overview');
        }

        return back()->withErrors([
            'username' => __('home/welcome.hm_invalid_login'),
        ], 'login')->onlyInput('username');
    }
}
