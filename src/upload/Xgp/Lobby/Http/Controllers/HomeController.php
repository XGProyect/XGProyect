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
namespace Xgp\Lobby\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Xgp\Lobby\Controllers\BaseController;

/**
 * Home controller
 */
class Home extends BaseController
{
    /**
     * @var App\Models\UserModel
     */
    private $userModel;

    /**
     * @var Xgp\Lobby\Models\HomeModel
     */
    private $homeModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        // load models
        $this->userModel = model('App\Models\UserModel');
        $this->homeModel = model('Xgp\Lobby\Models\HomeModel');

        // load languages
        $this->langLoad('home');
    }

    /**
     * Users land here
     *
     * @return void
     */
    public function index(): void
    {
        $this->page->withOptions([
            'topnav' => false,
            'menu' => false,
        ])->setData(array_merge(
            $this->lang->all(),
            $this->getPageData(),
            $this->getErrors()
        ))->display('index_body');
    }

    /**
     * Show the maintenance page
     *
     * @return void
     */
    public function maintenance(): void
    {
        $this->page->withOptions([
            'topnav' => false,
            'menu' => false,
        ])->setData(array_merge(
            $this->lang->all(),
            [
                'css_path' => LOBBY_CSS,
                'game_name' => strtr($this->lang->line('hm_title'), ['%s' => $this->setting->one('game_name')]),
                'close_title' => $this->lang->line('hm_server_closed'),
                'close_reason' => $this->getCloseReason(),
            ]
        ))->display('maintenance');
    }

    /**
     * Show the welcome content loaded through AJAX
     *
     * @return void
     */
    public function welcome(): void
    {
        $this->page->withOptions([
            'topnav' => false,
            'menu' => false,
        ])->setData(array_merge(
            $this->lang->all(),
            [
                'flash_path' => LOBBY_FLASH,
            ]
        ))->display('welcome');
    }

    /**
     * Show the about content loaded through AJAX
     *
     * @return void
     */
    public function about(): void
    {
        $this->page->withOptions([
            'topnav' => false,
            'menu' => false,
        ])->setData(
            $this->lang->all()
        )->display('info');
    }

    /**
     * Show the media content loaded through AJAX
     *
     * @return void
     */
    public function media(): void
    {
        $this->page->withOptions([
            'topnav' => false,
            'menu' => false,
        ])->setData(
            $this->lang->all()
        )->display('media');
    }

    /**
     * Start the player sign in
     *
     * @return RedirectResponse
     */
    public function signin(): RedirectResponse
    {
        $post = $this->request->getPost(['login', 'pass']);
        $url = base_url();

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

        return redirect()->to($url);
    }

    /**
     * Do the player signout
     *
     * @return RedirectResponse
     */
    public function signout(): RedirectResponse
    {
        $this->player->doLogout();

        return redirect()->to(base_url());
    }

    /**
     * Get the page data to fully parse it
     *
     * @return array
     */
    private function getPageData(): array
    {
        return [
            'servername' => strtr($this->lang->line('hm_title'), ['%s' => $this->setting->one('game_name')]),
            'css_path' => LOBBY_CSS,
            'js_path' => LOBBY_JS,
            'game_logo' => $this->setting->one('game_logo'),
            'img_path' => LOBBY_IMG,
            'base_path' => base_url(),
            'user_name' => $this->request->getGet('user'),
            'user_email' => $this->request->getGet('email'),
            'forum_url' => $this->setting->one('forum_url'),
            'version' => SYSTEM_VERSION,
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
        switch ($this->request->getGet('error')) {
            case 1:
                $div_id = '#username';
                $message = $this->lang->line('hm_username_not_available');
                break;

            case 2:
                $div_id = '#email';
                $message = $this->lang->line('hm_email_not_available');
                break;

            case 0:
            default:
                $div_id = '';
                $message = '';
                break;
        }

        return [
            'div_id' => $div_id,
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
