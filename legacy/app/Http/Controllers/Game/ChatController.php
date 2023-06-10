<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Messages;

class ChatController extends BaseController
{
    public const MODULE_ID = 18;

    private array $user = [];
    private array $_receiver_data = [];
    private array $_message_data = [];
    private Messages $messagesModel;

    public function __invoke()
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->messagesModel = new Messages();

        $this->runAction();

        Template::getInstance()->view(
            'chat.view',
            [
                'id' => $this->_receiver_data['user_id'],
                'to' => $this->_receiver_data['user_name'] . ' ' . FormatLib::prettyCoords(
                    $this->_receiver_data['planet_galaxy'],
                    $this->_receiver_data['planet_system'],
                    $this->_receiver_data['planet_planet']
                ),
                'subject' => ((!isset($this->_message_data['subject'])) ? __('game/chat.pm_no_subject') : $this->_message_data['subject']),
                'text' => ((!isset($this->_message_data['text'])) ? '' : $this->_message_data['text']),
                'error_text' => ((!isset($this->_message_data['error_text'])) ? '' : $this->_message_data['error_text']),
                'error_color' => ((!isset($this->_message_data['error_color'])) ? '' : $this->_message_data['error_color']),
            ]
        );
    }

    private function runAction(): void
    {
        $write_to = filter_input(INPUT_GET, 'playerId', FILTER_VALIDATE_INT);
        $message_sent = filter_input_array(INPUT_POST);

        if ($write_to) {
            $this->_receiver_data = $this->messagesModel->getHomePlanet($write_to);

            if (!$this->_receiver_data) {
                Functions::redirect('game.php?page=messages');
            }
        }

        $this->_message_data['error_block'] = false;
        $this->_message_data['error_text'] = __('game/chat.pm_msg_sended');
        $this->_message_data['error_color'] = '#00FF00';

        if ($message_sent) {
            $errors = 0;
            $this->_message_data['error_block'] = true;
            $this->_message_data['subject'] = $message_sent['subject'];
            $this->_message_data['text'] = $message_sent['text'];

            if (!$message_sent['subject']) {
                $errors++;
                $this->_message_data['error_text'] = __('game/chat.pm_no_subject');
                $this->_message_data['error_color'] = '#FF0000';
            }

            if (!$message_sent['text']) {
                $errors++;
                $this->_message_data['error_text'] = __('game/chat.pm_no_text');
                $this->_message_data['error_color'] = '#FF0000';
            }

            if ($errors == 0) {
                $this->_message_data['subject'] = '';
                $this->_message_data['text'] = '';

                Functions::sendMessage(
                    $write_to,
                    $this->user['user_id'],
                    0,
                    4,
                    $this->user['user_name'] . ' ' . FormatLib::prettyCoords(
                        $this->user['user_galaxy'],
                        $this->user['user_system'],
                        $this->user['user_planet']
                    ),
                    $message_sent['subject'],
                    $message_sent['text']
                );
            }
        }
    }
}
