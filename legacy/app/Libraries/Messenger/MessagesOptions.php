<?php

namespace Xgp\App\Libraries\Messenger;

use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Helpers\StringsHelper;

final class MessagesOptions
{
    private int $_to;
    private int $_sender;
    private int $_time;
    private int $_type;
    private string $_from;
    private string $_subject;
    private string $_message_text;
    private int $_message_format = MessagesFormat::SIMPLE;

    public function getTo(): int
    {
        return $this->_to;
    }

    public function getSender(): int
    {
        return $this->_sender == 0 ? 0 : $this->_sender;
    }

    public function getTime(): int
    {
        return $this->_time === 0 ? time() : $this->_time;
    }

    public function getType(): int
    {
        if ($this->_type == 0 or !is_object($this->_type)) {
            return MessagesEnumerator::GENERAL;
        }

        return $this->_type;
    }

    public function getFrom(): string
    {
        return $this->_from;
    }

    public function getSubject(): string
    {
        return $this->_subject;
    }

    public function getMessageText(): string
    {
        return $this->_message_text;
    }

    public function getMessageFormat(): int
    {
        if ($this->_message_format == 0) {
            return MessagesFormat::SIMPLE;
        }

        return $this->_message_format;
    }

    public function setTo(int $to)
    {
        $this->_to = $to;
    }

    public function setSender(int $sender)
    {
        $this->_sender = $sender;
    }

    public function setTime(int $time)
    {
        $this->_time = $time;
    }

    public function setType(int $type)
    {
        $this->_type = $type;
    }

    public function setFrom(string $from)
    {
        $this->_from = $from;
    }

    public function setSubject(string $subject)
    {
        $this->_subject = $subject;
    }

    public function setMessageText(string $message_text)
    {
        if ($this->_message_format == MessagesFormat::HTML) {
            $this->_message_text = stripslashes($message_text);
        } else {
            $this->_message_text = StringsHelper::escapeString($message_text);
        }
    }

    public function setMessageFormat(int $message_format)
    {
        $this->_message_format = $message_format;
    }
}
