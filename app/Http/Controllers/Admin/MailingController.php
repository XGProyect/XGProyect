<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MailingRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MailingController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        return $this->view('admin.mailing', $this->buildViewData());
    }

    public function update(MailingRequest $request): RedirectResponse
    {
        foreach ($request->toSettings() as $key => $value) {
            $this->settings->write($key, $value);
        }

        return $this->saved('admin.mailing', 'admin/mailing.ma_all_ok_message');
    }

    private function buildViewData(): array
    {
        $protocol = $this->settings->getString('mailing_protocol');
        $crypto = $this->settings->getString('mailing_smtp_crypto');

        return [
            'mailing_protocol' => $protocol,
            'mailing_smtp_host' => $this->settings->getString('mailing_smtp_host'),
            'mailing_smtp_user' => $this->settings->getString('mailing_smtp_user'),
            'mailing_smtp_pass' => $this->settings->getString('mailing_smtp_pass'),
            'mailing_smtp_port' => $this->settings->getInt('mailing_smtp_port'),
            'mailing_smtp_timeout' => $this->settings->getInt('mailing_smtp_timeout'),
            'mailing_smtp_crypto' => $crypto,
            'protocol_options' => $this->buildSelectOptions(['mail', 'sendmail', 'smtp'], $protocol, 'strtoupper'),
            'smtp_crypto_options' => [
                ['value' => '',    'label' => 'None', 'selected' => $crypto === ''],
                ['value' => 'tls', 'label' => 'TLS',  'selected' => $crypto === 'tls'],
                ['value' => 'ssl', 'label' => 'SSL',  'selected' => $crypto === 'ssl'],
            ],
        ];
    }

    /**
     * @param  string[]       $items
     * @param  callable|null  $labelFn  optional transform applied to each label
     *
     * @return array<int, array{value: string, label: string, selected: bool}>
     */
    private function buildSelectOptions(array $items, string $current, ?string $labelFn = null): array
    {
        return array_map(fn ($item) => [
            'value' => $item,
            'label' => $labelFn ? $labelFn($item) : $item,
            'selected' => $item === $current,
        ], $items);
    }
}
