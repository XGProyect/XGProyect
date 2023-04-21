<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Page;

class MailingController extends BaseController
{
    public const MAILING_SETTINGS = [
        'mailing_protocol' => FILTER_UNSAFE_RAW,
        'mailing_smtp_host' => FILTER_UNSAFE_RAW,
        'mailing_smtp_user' => FILTER_UNSAFE_RAW,
        'mailing_smtp_pass' => FILTER_UNSAFE_RAW,
        'mailing_smtp_port' => FILTER_VALIDATE_INT,
        'mailing_smtp_timeout' => FILTER_VALIDATE_INT,
        'mailing_smtp_crypto' => FILTER_UNSAFE_RAW,
    ];

    private string $alert = '';

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            die(Administration::noAccessMessage(__('admin/global.no_permissions')));
        }

        // time to do something
        $this->runAction();

        // build the page
        $this->buildPage();
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, self::MAILING_SETTINGS);

        if ($data) {
            foreach ($data as $option => $value) {
                if ((is_numeric($value) && $value >= 0) or is_string($value) && ($value !== false && $value !== null)) {
                    Functions::updateConfig($option, $value);
                }
            }

            $this->alert = Administration::saveMessage('ok', $this->langs->line('pr_all_ok_message'));
        }
    }

    private function buildPage(): void
    {
        Page::getInstance()->displayAdmin(
            Template::getInstance()->render(
                'admin.mailing_view',
                array_merge(
                    $this->getMailingSettings(),
                    $this->buildProtocolsDropdown(),
                    $this->buildCryptoDropdown(),
                    [
                        'alert' => $this->alert ?? '',
                    ]
                )
            )
        );
    }

    private function getMailingSettings(): array
    {
        return array_filter(
            Functions::readConfig('', true),
            function ($key) {
                return array_key_exists($key, self::MAILING_SETTINGS);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function buildProtocolsDropdown(): array
    {
        $options = [];

        foreach (['mail', 'sendmail', 'smtp'] as $option) {
            $options[] = [
                'value' => $option,
                'selected' => ($option == Functions::readConfig('mailing_protocol') ? ' selected' : ''),
                'option' => $option,
            ];
        }

        return ['protocol_options' => $options];
    }

    private function buildCryptoDropdown(): array
    {
        $options = [];

        foreach (['', 'tls', 'ssl'] as $option) {
            $options[] = [
                'value' => $option,
                'selected' => ($option == Functions::readConfig('mailing_smtp_crypto') ? ' selected' : ''),
                'option' => strtoupper($option),
            ];
        }

        return ['smtp_crypto_options' => $options];
    }
}
