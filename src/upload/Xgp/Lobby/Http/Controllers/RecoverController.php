<?php declare (strict_types = 1);
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

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecoverController extends Controller
{
    /**
     * @var App\Models\UserModel
     */
    private $userModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        // load models
        $this->userModel = model('App\Models\UserModel');

        // load languages
        $this->langLoad('recover');

        // set default view path
        $this->view_location = 'Xgp\Lobby\Views\\';
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
            [
                'gameName' => $this->setting->one('game_name'),
                'display' => $this->request->getGet('send') == '' ? 'display: none' : 'display: block',
                'errorMsg' => $this->request->getGet('send') == 'ok' ? $this->lang->line('ma_sent') : $this->lang->line('ma_error'),
                'maSendPwdTitle' => strtr($this->lang->line('ma_send_pwd_title'), ['%s' => $this->setting->one('game_name')]),
            ]
        ))->display($this->view_location . 'recover');
    }

    /**
     * Start the process to recover the player password
     *
     * @return RedirectResponse
     */
    public function request(): RedirectResponse
    {
        $url = url('/') . '/recover';
        $result = '';
        $this->post = $this->request->getPost(['email']);

        if (isset($this->post)) {
            $result = '?send=error';

            if ($this->validation->run($this->post, 'recover')) {
                if ($this->processRequest()) {
                    $result = '?send=ok';
                }
            }
        }

        return redirect()->to($url . $result);
    }

    /**
     * Process the request
     *
     * @return boolean
     */
    private function processRequest(): bool
    {
        $userName = $this->userModel->getUsernameByEmail($this->post['email']);

        if ($userName) {
            helper('text_helper');

            $newPassword = random_string('alnum', 16);

            if ($this->sendPassword($userName, $newPassword)) {
                $this->userModel->updatePassword($this->post['email'], $newPassword);

                return true;
            }
        }

        return false;
    }

    /**
     * Send an email to the player with their new password
     *
     * @param string $newPassword
     * @return void
     */
    private function sendPassword(string $userName, string $newPassword): void
    {
        $gameName = $this->setting->one('game_name');

        (new Email)
            ->from($this->setting->one('admin_email'), $gameName)
            ->to($this->post['email'])
            ->subject(sprintf($this->lang->line('ma_mail_subject'), $gameName))
            ->template(
                $this->view_location . 'recover_password_email_template',
                array_merge(
                    $this->lang->all(),
                    [
                        'mail_title' => sprintf($this->lang->line('ma_mail_title'), $userName),
                        'user_pass' => $newPassword,
                        'game_url' => GAMEURL,
                        'ma_mail_team' => strtr($this->lang->line('ma_mail_team'), ['%s' => $this->setting->one('game_name')]),
                    ]
                )
            )
            ->send();
    }
}
