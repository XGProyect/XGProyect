<?php
/**
 * XG Proyect
 *
 * Open-source OGame Clon
 *
 * This content is released under the GPL-3.0 License
 *
 * Copyright (c) 2008-2021 XG Proyect
 *
 * @package    XG Proyect
 * @author     XG Proyect Team
 * @copyright  2008-2021 XG Proyect
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0 License
 * @link       https://github.com/XGProyect/
 * @since      4.0.0
 */
namespace Xgp\Lobby\Http\Controllers;

use App\Libraries\User\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Xgp\Lobby\Entities\Home;
use Xgp\Lobby\Http\Requests\Signin;

class HomeController extends Controller
{
    /**
     * Users land here
     *
     * @return void
     */
    public function index(): View
    {
        return view('lobby::home')->with(array_merge(
            $this->getPageData(),
            $this->getErrors()
        ));
    }

    /**
     * Show the maintenance page
     *
     * @return void
     */
    public function maintenance(): View
    {
        return view('lobby::maintenance')->with([
            'gameName' => strtr(__('lobby::home.hm_title'), ['%s' => config('settings.game_name')]),
            'closeTitle' => __('lobby::home.hm_server_closed'),
            'closeReason' => $this->getCloseReason(),
        ]);
    }

    /**
     * Show the welcome content loaded through AJAX
     *
     * @return void
     */
    public function welcome(): View
    {
        return view('lobby::welcome');
    }

    /**
     * Show the about content loaded through AJAX
     *
     * @return void
     */
    public function about(): View
    {
        return view('lobby::info');
    }

    /**
     * Show the media content loaded through AJAX
     *
     * @return void
     */
    public function media(): View
    {
        return view('lobby::media');
    }

    /**
     * Start the player sign in
     *
     * @return RedirectResponse
     */
    public function signin(Signin $request): RedirectResponse
    {
        $url = url('/');
        $data = $request->validated();
        $player = (new Home)->getUserWithProvidedCredentials($data['login']);

        if (isset($player) && Hash::check($data['pass'], $player->user_password)) {
            if ((new Player)->doLogin($player->user_id, $player->user_password)) {
                (new User)->setCurrentPlanet($player->user_id);
                $url .= '/game/index.php?page=overview&sessionId=' . session()->getId();
            }
        }

        return redirect($url);
    }

    /**
     * Do the player signout
     *
     * @return RedirectResponse
     */
    public function signout(): RedirectResponse
    {
        (new Player)->doLogout();

        return redirect('/');
    }

    /**
     * Get the page data to fully parse it
     *
     * @return array
     */
    private function getPageData(): array
    {
        return [
            'servername' => __('lobby::home.hm_title', ['game_name' => config('settings.game_name')]),
            'gameLogo' => config('settings.game_logo'),
            'forumUrl' => config('settings.forum_url'),
            'version' => config('system.version'),
            'year' => date('Y'),
        ];
    }

    /**
     * Get the error data
     *
     * @return array
     */
    private function getErrors(): array
    {
        if ($errors = session()->get('errors')) {
            if ($nameTaken = $errors->first('character')) {
                $divId = '#username';
                $message = $nameTaken;
            }

            if ($emailTaken = $errors->first('email')) {
                $divId = '#email';
                $message = $emailTaken;
            }
        }

        return [
            'divId' => $divId ?? '',
            'message' => $message ?? '',
        ];
    }

    /**
     * Get the close reason
     *
     * @return string
     */
    private function getCloseReason(): string
    {
        if (config('settings.game_close') == 1) {
            return config('settings.close_reason');
        }

        if (config('settings.reg_enable') != 1) {
            return __('lobby::home.hm_reg_close');
        }

        return '';
    }
}
