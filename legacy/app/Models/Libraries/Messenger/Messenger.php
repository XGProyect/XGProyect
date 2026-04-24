<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries\Messenger;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Messenger\MessagesOptions;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Messenger
{
    use PreparesLegacySql;

    public function insertMessage(MessagesOptions $options): void
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
