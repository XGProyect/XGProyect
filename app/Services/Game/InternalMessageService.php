<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Messages;

/**
 * Replacement for the legacy Functions::sendMessage() helper. Lives in
 * App\Services so future migrated controllers (Chat, Messages, Alliance…)
 * can reuse it without dragging the Xgp\App\Libraries\Functions class.
 *
 * Message "types" mirror MessagesEnumerator constants from the legacy
 * codebase to keep cross-system compatibility while the inbox is still
 * legacy.
 */
class InternalMessageService
{
    public const TYPE_ESPIO = 0;
    public const TYPE_COMBAT = 1;
    public const TYPE_EXPEDITION = 2;
    public const TYPE_ALLIANCE = 3;
    public const TYPE_USER = 4;
    public const TYPE_GENERAL = 5;

    public function send(
        int $receiverId,
        int $senderId,
        string $fromName,
        string $subject,
        string $body,
        int $type = self::TYPE_GENERAL,
        ?int $time = null,
    ): Messages {
        return Messages::create([
            'message_sender' => $senderId,
            'message_receiver' => $receiverId,
            'message_time' => $time ?? time(),
            'message_type' => $type,
            'message_from' => $fromName,
            'message_subject' => $subject,
            'message_text' => $body,
            'message_read' => 0,
        ]);
    }
}
