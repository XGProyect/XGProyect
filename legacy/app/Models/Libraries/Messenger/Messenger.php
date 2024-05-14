<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries\Messenger;

use Xgp\App\Core\Model;
use Xgp\App\Libraries\Messenger\MessagesOptions;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Messenger extends Model
{
    /**
     * Insert a new message
     *
     * @param MessagesOptions $options
     * @return void
     */
    public function insertMessage(MessagesOptions $options): void
    {
        $this->db->query(
            'INSERT INTO `' . MESSAGES . "` SET
            `message_receiver` = '" . $options->getTo() . "',
            `message_sender` = '" . $options->getSender() . "',
            `message_time` = '" . $options->getTime() . "',
            `message_type` = '" . $options->getType() . "',
            `message_from` = '" . $options->getFrom() . "',
            `message_subject` = '" . $options->getSubject() . "',
            `message_text` 	= '" . $options->getMessageText() . "';"
        );
    }
}
