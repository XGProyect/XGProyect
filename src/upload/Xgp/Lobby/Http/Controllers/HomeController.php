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
 * @since      Version 4.0.0
 */
namespace Xgp\Lobby\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

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
            'gameName' => strtr(__('lobby::home.hm_title'), ['%s' => $this->setting->one('game_name')]),
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
    public function signin(): RedirectResponse
    {
        $post = $this->request->getPost(['login', 'pass']);
        $url = url('/')();

        if (isset($post)) {
            if ($this->validation->run($post, 'signin')) {
                $login = $this->homeModel->getUserWithProvidedCredentials($post['login']);

                if (isset($login) && password_verify($post['pass'], $login->user_password)) {
                    if ($this->player->doLogin($login->user_id, $login->user_password)) {
                        $this->userModel->setCurrentPlanet($login->user_id);
                        $url .= '/game/index.php?page=overview&sessionId=' . session_id();
                    }
                }
            }
        }

        return redirect()->route($url);
    }

    /**
     * Do the player signout
     *
     * @return RedirectResponse
     */
    public function signout(): RedirectResponse
    {
        $this->player->doLogout();

        return redirect()->route('/');
    }

    /**
     * Get the page data to fully parse it
     *
     * @return array
     */
    private function getPageData(): array
    {
        return [
            //'servername' => strtr(__('lobby::home.hm_title'), ['%s' => $this->setting->one('game_name')]),
            //'gameLogo' => $this->setting->one('game_logo'),
            //'userName' => $this->request->getGet('user'),
            //'userEmail' => $this->request->getGet('email'),
            //'forumUrl' => $this->setting->one('forum_url'),
            //'version' => SYSTEM_VERSION,
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
        switch (0) {
            case 1:
                $divId = '#username';
                $message = __('lobby::home.hm_username_not_available');
                break;

            case 2:
                $divId = '#email';
                $message = __('lobby::home.hm_email_not_available');
                break;

            case 0:
            default:
                $divId = '';
                $message = '';
                break;
        }

        return [
            'divId' => $divId,
            'message' => $message,
        ];
    }

    /**
     * Get the close reason
     *
     * @return string
     */
    private function getCloseReason(): string
    {
        if ($this->setting->one('game_close') == 1) {
            return $this->setting->one('close_reason');
        }

        if ($this->setting->one('reg_enable') != 1) {
            return $this->lang->line('hm_reg_close');
        }

        return '';
    }
}
