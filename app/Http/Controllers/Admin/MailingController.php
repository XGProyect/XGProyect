<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;

class MailingController extends BaseController
{
    private AdministrationService $administrationService;

    public function __construct(private readonly SettingsService $settings)
    {
        $this->administrationService = new AdministrationService($settings);
    }

    public function index(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        Template::legacyView('admin.mailing', $this->buildViewData());
    }

    public function update(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        if ($request->filled('mailing_protocol')) {
            $this->settings->write('mailing_protocol', $request->input('mailing_protocol'));
        }

        if ($request->filled('mailing_smtp_host')) {
            $this->settings->write('mailing_smtp_host', $request->input('mailing_smtp_host'));
        }

        if ($request->filled('mailing_smtp_user')) {
            $this->settings->write('mailing_smtp_user', $request->input('mailing_smtp_user'));
        }

        // Allow empty password (don't skip blank on purpose — use has() not filled())
        if ($request->has('mailing_smtp_pass')) {
            $this->settings->write('mailing_smtp_pass', $request->input('mailing_smtp_pass'));
        }

        if ($request->filled('mailing_smtp_port') && is_numeric($request->input('mailing_smtp_port'))) {
            $this->settings->write('mailing_smtp_port', (int) $request->input('mailing_smtp_port'));
        }

        if ($request->filled('mailing_smtp_timeout') && is_numeric($request->input('mailing_smtp_timeout'))) {
            $this->settings->write('mailing_smtp_timeout', (int) $request->input('mailing_smtp_timeout'));
        }

        // Allow empty (None) encryption
        if ($request->has('mailing_smtp_crypto')) {
            $this->settings->write('mailing_smtp_crypto', $request->input('mailing_smtp_crypto', ''));
        }

        return redirect()->route('admin.mailing')
            ->with('success', __('admin/mailing.ma_all_ok_message'));
    }

    private function buildViewData(): array
    {
        return [
            'mailing_protocol'    => $this->settings->getString('mailing_protocol'),
            'mailing_smtp_host'   => $this->settings->getString('mailing_smtp_host'),
            'mailing_smtp_user'   => $this->settings->getString('mailing_smtp_user'),
            'mailing_smtp_pass'   => $this->settings->getString('mailing_smtp_pass'),
            'mailing_smtp_port'   => $this->settings->getInt('mailing_smtp_port'),
            'mailing_smtp_timeout' => $this->settings->getInt('mailing_smtp_timeout'),
            'mailing_smtp_crypto' => $this->settings->getString('mailing_smtp_crypto'),
            'protocol_options'    => $this->buildProtocolOptions(),
            'smtp_crypto_options' => $this->buildCryptoOptions(),
        ];
    }

    private function buildProtocolOptions(): array
    {
        $current = $this->settings->getString('mailing_protocol');

        return array_map(fn ($option) => [
            'value'    => $option,
            'label'    => strtoupper($option),
            'selected' => $option === $current,
        ], ['mail', 'sendmail', 'smtp']);
    }

    private function buildCryptoOptions(): array
    {
        $current = $this->settings->getString('mailing_smtp_crypto');

        return [
            ['value' => '',    'label' => 'None', 'selected' => $current === ''],
            ['value' => 'tls', 'label' => 'TLS',  'selected' => $current === 'tls'],
            ['value' => 'ssl', 'label' => 'SSL',  'selected' => $current === 'ssl'],
        ];
    }
}
