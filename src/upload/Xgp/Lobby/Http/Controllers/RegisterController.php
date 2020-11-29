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

use App\Libraries\Email;
use App\Libraries\Messaging;
use App\Libraries\Planet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Xgp\Lobby\Entities\Register;
use Xgp\Lobby\Http\Requests\Signup;

class RegisterController extends Controller
{
    /**
     * Contains the registration submit data
     *
     * @var array
     */
    private $post;

    /**
     * Users land here
     *
     * @return RedirectResponse
     */
    public function index(Signup $request): RedirectResponse
    {
        $url = url('/');
        $this->post = $request->validated();

        if ($this->doSignup()) {
            $url .= '/game/index.php?page=overview&sessionId=' . session()->getId();
        }

        return redirect($url);
    }

    /**
     * Register a new user and all the necessary things for them
     *
     * @return boolean
     */
    private function doSignup(): bool
    {
        $newUserId = (new Register)->createNewUser(
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
        if (config('settings.reg_welcome_message')) {
            $message = new Messaging;
            $message->receiver($newUserId);
            $message->from($this->lang->line('re_welcome_message_from'));
            $message->subject($this->lang->line('re_welcome_message_subject'));
            $message->text(sprintf($this->lang->line('re_welcome_message_content'), $this->post['character']));
            $message->send();
        }

        // send welcome email to the user if the feature is enabled
        if (config('settings.reg_welcome_email')) {
            $this->sendWelcome();
        }

        $this->player->doLogin($newUserId, (new User)->find($newUserId)->user_password);
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
        $gameName = config('settings.game_name');

        (new Email)
            ->from(config('settings.admin_email'), $gameName)
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
