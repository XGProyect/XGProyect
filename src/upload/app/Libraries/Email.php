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
 * @since      4.0.0
 */
namespace App\Libraries;

use Config\DBSettings;
use Config\Services;

/**
 * Email class
 */
class Email
{
    /**
     * Contains the Email class
     *
     * @var \CodeIgniter\Email\Email
     */
    private $email;

    /**
     * Contains the settings
     *
     * @var DBSettings
     */
    private $setting;

    /**
     * Contains the template
     *
     * @var string
     */
    private $template;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->email = Services::email();
        $this->setting = new DBSettings;

        $this->initialize();
    }

    /**
     * Set the email from
     *
     * @param string $email_address
     * @param string $sender_name
     * @return Email
     */
    public function from(string $email_address, string $sender_name): Email
    {
        $this->email->setFrom($email_address, $sender_name);

        return $this;
    }

    /**
     * Set the email to
     *
     * @param string $email_address
     * @return Email
     */
    public function to(string $email_address): Email
    {
        $this->email->setTo($email_address);

        return $this;
    }

    /**
     * Set the email subject
     *
     * @return Email
     */
    public function subject(string $subject): Email
    {
        $this->email->setSubject($subject);

        return $this;
    }

    /**
     * Set a template and the data to parse on the email
     *
     * @param string $template
     * @return Email
     */
    public function template(string $template_location, array $data): Email
    {
        $this->message((new Template)->set($template_location, $data));

        return $this;
    }

    /**
     * Set the email message, if this method is used the template method will be ignored
     *
     * @return Email
     */
    public function message(string $message): Email
    {
        $this->email->setMessage($message);

        return $this;
    }

    /**
     * Send the email
     *
     * @return boolean
     */
    public function send(): bool
    {
        return $this->email->send();
    }

    /**
     * Initialize CI email class with our settings
     *
     * @return void
     */
    private function initialize(): void
    {
        $config['protocol'] = $this->setting->one('mailing_protocol');

        if ($this->setting->one('mailing_protocol') === 'smtp') {
            $config['SMTPHost'] = $this->setting->one('mailing_smtp_host');
            $config['SMTPUser'] = $this->setting->one('mailing_smtp_user');
            $config['SMTPPass'] = $this->setting->one('mailing_smtp_pass');
            $config['SMTPPort'] = $this->setting->one('mailing_smtp_port');
            $config['SMTPTimeout'] = $this->setting->one('mailing_smtp_timeout');
            $config['SMTPCrypto'] = $this->setting->one('mailing_smtp_crypto');
        }

        $this->email->initialize($config);
    }
}
