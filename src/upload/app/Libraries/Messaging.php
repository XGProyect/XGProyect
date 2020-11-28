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
namespace App\Libraries;

use App\Entities\Message;
use App\Models\MessageModel;

/**
 * Messaging class
 */
class Messaging
{
    /**
     * Contains the message sender
     *
     * @var int
     */
    private $sender;

    /**
     * Contains the message receiver
     *
     * @var int
     */
    private $receiver;

    /**
     * Contains the message time and date
     *
     * @var int
     */
    private $time;

    /**
     * Contains the message type
     *
     * @var int
     */
    private $type;

    /**
     * Contains the message from
     *
     * @var string
     */
    private $from;

    /**
     * Contains the message subject
     *
     * @var string
     */
    private $subject;

    /**
     * Contains the message text
     *
     * @var string
     */
    private $text;

    /**
     * Send a new message. Ypu must fill first all the  optionally you can pass a message object and if all fields are correct a message will be sent.
     *
     * @param Message|null $message
     * @return boolean
     */
    public function send(?Message $message = null): bool
    {
        if (is_null($message)) {
            $message = new Message;
            $message->message_sender = $this->getSender();
            $message->message_receiver = $this->getReceiver();
            $message->message_time = $this->getTime();
            $message->message_type = $this->getType();
            $message->message_from = $this->getFrom();
            $message->message_subject = $this->getSubject();
            $message->message_text = $this->getText();
        }

        return (new MessageModel)->save($message);
    }

    /**
     * Set the message sender
     *
     * @param integer $sender
     * @return void
     */
    public function sender(int $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * Set the message receiver
     *
     * @param integer $receiver
     * @return void
     */
    public function receiver(int $receiver): void
    {
        $this->receiver = $receiver;
    }

    /**
     * Set the message time, if not provided defaults to current time
     *
     * @param integer $time
     * @return void
     */
    public function time(int $time): void
    {
        $this->time = $time;
    }

    /**
     * Set the type of message
     *
     * @param integer $type
     * @return void
     */
    public function type(int $type): void
    {
        $this->type = $type;
    }

    /**
     * Set the message from label
     *
     * @param string $from
     * @return void
     */
    public function from(string $from): void
    {
        $this->from = $from;
    }

    /**
     * Set the message subject
     *
     * @param string $subject
     * @return void
     */
    public function subject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Set the message text
     *
     * @param string $text
     * @return void
     */
    public function text(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Get the message sender
     *
     * @return integer
     */
    public function getSender(): int
    {
        return $this->sender ?? 1;
    }

    /**
     * Get the message receiver
     *
     * @return integer
     */
    public function getReceiver(): int
    {
        return $this->receiver;
    }

    /**
     * Get the message time
     *
     * @return integer
     */
    public function getTime(): int
    {
        return $this->time ?? time();
    }

    /**
     * Get the type of message
     *
     * @return integer
     */
    public function getType(): int
    {
        return $this->type ?? 5;
    }

    /**
     * Get the message from label
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Get the message subject
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject ?? '';
    }

    /**
     * Get the message content
     *
     * @param string $text
     * @return void
     */
    public function getText(): string
    {
        return $this->text;
    }
}
