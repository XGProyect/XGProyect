<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class ChatController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $_receiver_data = [];
    private array $_message_data = [];

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Messages));

        $this->user = Users::getInstance()->getUserData();

        $this->runAction();

        Template::legacyView(
            'chat.view',
            [
                'id' => $this->_receiver_data['id'],
                'to' => $this->_receiver_data['name'] . ' ' . $this->formatService->prettyCoords(
                    (int) $this->_receiver_data['planet_galaxy'],
                    (int) $this->_receiver_data['planet_system'],
                    (int) $this->_receiver_data['planet_planet']
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
            $row = (int) $write_to > 0 ? DB::selectOne(
                $this->prepareSql(
                    'SELECT u.`id`, u.`name`, p.`planet_galaxy`, p.`planet_system`, p.`planet_planet`
                    FROM ' . PLANETS . ' AS p
                    INNER JOIN ' . USERS . " as u ON p.planet_user_id = u.id
                    WHERE p.`planet_user_id` = '" . (int) $write_to . "';"
                )
            ) : null;
            $this->_receiver_data = $row !== null ? (array) $row : null;

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
                    $this->user['id'],
                    0,
                    4,
                    $this->user['name'] . ' ' . $this->formatService->prettyCoords(
                        (int) $this->user['galaxy'],
                        (int) $this->user['system'],
                        (int) $this->user['planet']
                    ),
                    $message_sent['subject'],
                    $message_sent['text']
                );
            }
        }
    }
}
