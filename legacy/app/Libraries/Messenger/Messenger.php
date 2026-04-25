<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Messenger;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
final class Messenger
{
    use PreparesLegacySql;

    public function sendMessage(MessagesOptions $options): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . MESSAGES . "` SET
                `message_receiver` = '" . $options->getTo() . "',
                `message_sender` = '" . $options->getSender() . "',
                `message_time` = '" . $options->getTime() . "',
                `message_type` = '" . $options->getType() . "',
                `message_from` = '" . $options->getFrom() . "',
                `message_subject` = '" . $options->getSubject() . "',
                `message_text` 	= '" . $options->getMessageText() . "';"
            )
        );
    }
}
