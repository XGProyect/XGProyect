<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MailingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mailing_protocol' => ['nullable', 'string', 'in:mail,sendmail,smtp'],
            'mailing_smtp_host' => ['nullable', 'string', 'max:254'],
            'mailing_smtp_user' => ['nullable', 'string', 'max:254'],
            'mailing_smtp_pass' => ['nullable', 'string'],
            'mailing_smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mailing_smtp_timeout' => ['nullable', 'integer', 'min:1'],
            'mailing_smtp_crypto' => ['nullable', 'string', 'in:,tls,ssl'],
        ];
    }

    /**
     * Returns a flat map of setting key => value ready to persist.
     * Handles the two fields that use has() instead of filled()
     * (empty string is a valid value for both pass and crypto).
     *
     * @return array<string, mixed>
     */
    public function toSettings(): array
    {
        $validated = $this->validated();
        $settings = [];

        if (isset($validated['mailing_protocol'])) {
            $settings['mailing_protocol'] = $validated['mailing_protocol'];
        }
        if (isset($validated['mailing_smtp_host'])) {
            $settings['mailing_smtp_host'] = $validated['mailing_smtp_host'];
        }
        if (isset($validated['mailing_smtp_user'])) {
            $settings['mailing_smtp_user'] = $validated['mailing_smtp_user'];
        }
        if ($this->has('mailing_smtp_pass')) {
            $settings['mailing_smtp_pass'] = (string) $this->input('mailing_smtp_pass');
        }
        if (isset($validated['mailing_smtp_port'])) {
            $settings['mailing_smtp_port'] = $validated['mailing_smtp_port'];
        }
        if (isset($validated['mailing_smtp_timeout'])) {
            $settings['mailing_smtp_timeout'] = $validated['mailing_smtp_timeout'];
        }
        if ($this->has('mailing_smtp_crypto')) {
            $settings['mailing_smtp_crypto'] = (string) $this->input('mailing_smtp_crypto', '');
        }

        return $settings;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.mailing'));
    }
}
