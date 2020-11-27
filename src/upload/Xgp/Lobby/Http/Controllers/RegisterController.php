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

namespace Xgp\Lobby\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RegisterController extends Controller
{
    /**
     * @var App\Models\UserModel
     */
    private $userModel;

    /**
     * @var Xgp\Lobby\Models\RegisterModel
     */
    private $registerModel;

    /**
     * Contains the registration submit data
     *
     * @var array
     */
    private $post;

    /**
     * Constructor
     */
    public function __construct()
    {
        // load models
        $this->userModel = model('App\Models\UserModel');
        $this->registerModel = model('Xgp\Lobby\Models\RegisterModel');

        // load languages
        $this->langLoad('register');

        // set default view path
        $this->view_location = 'Xgp\Lobby\Views\\';
    }

    /**
     * Users land here
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        $url = url('/');
        $this->post = $this->request->getPost(['character', 'email', 'password', 'agb']);

        if (isset($this->post)) {
            if ($this->validation->run($this->post, 'signup')) {
                if ($this->doSignup()) {
                    $url .= '/game/index.php?page=overview&sessionId=' . session_id();
                }
            } else {
                $url .= '?user=' . $this->post['character'] . '&email=' . $this->post['email'] . '&error=' . $this->setErrorId();
            }
        }

        return redirect()->to($url);
    }

    /**
     * Set the error ID
     *
     * @return string
     */
    private function setErrorId(): string
    {
        $errors = $this->validation->getErrors();

        if ($errors) {
            if (isset($errors['character']) == 1) {
                return 1;
            }

            if (isset($errors['email']) == 2) {
                return 2;
            }
        }

        return '';
    }

    /**
     * Register a new user and all the necessary things for them
     *
     * @return boolean
     */
    private function doSignup(): bool
    {
        $newUserId = $this->registerModel->createNewUser(
            [
                'user_name' => $this->post['character'],
                'user_email' => $this->post['email'],
                'user_password' => $this->post['password'],
            ],
            (new Planet)->getNewPlanetPosition()
        );

        if ($newUserId == 0) {
            return false;
        }

        // send welcome message to the user if the feature is enabled
        if ($this->setting->one('reg_welcome_message')) {
            $message = new Messaging;
            $message->receiver($newUserId);
            $message->from($this->lang->line('re_welcome_message_from'));
            $message->subject($this->lang->line('re_welcome_message_subject'));
            $message->text(sprintf($this->lang->line('re_welcome_message_content'), $this->post['character']));
            $message->send();
        }

        // send welcome email to the user if the feature is enabled
        if ($this->setting->one('reg_welcome_email')) {
            $this->sendWelcome();
        }

        $this->player->doLogin($newUserId, (new UserModel)->find($newUserId)->user_password);
        $this->userModel->setCurrentPlanet($newUserId);

        return true;
    }

    /**
     * Send a welcome email to the new player
     *
     * @return void
     */
    private function sendWelcome(): void
    {
        $gameName = $this->setting->one('game_name');

        (new Email)
            ->from($this->setting->one('admin_email'), $gameName)
            ->to($this->post['email'])
            ->subject(sprintf($this->lang->line('re_mail_register_at'), $gameName))
            ->template(
                $this->view_location . 'welcome_email_template',
                array_merge(
                    $this->lang->all(),
                    [
                        'welcome_text' => sprintf($this->lang->line('re_welcome_text'), $gameName),
                        'user_name' => $this->post['email'],
                        'user_pass' => $this->post['password'],
                        'game_url' => GAMEURL,
                        're_mail_text_part1' => str_replace('%s', $gameName, $this->lang->line('re_mail_text_part1')),
                        're_mail_text_part7' => str_replace('%s', $gameName, $this->lang->line('re_mail_text_part7')),
                    ]
                )
            )
            ->send();
    }
}
