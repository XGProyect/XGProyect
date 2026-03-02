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
     * String fields (host, user, pass, crypto) use has() so that submitting
     * an empty value clears the stored setting instead of skipping it.
     *
     * @return array<string, mixed>
     */
    public function toSettings(): array
    {
        $settings = [];

        if ($this->has('mailing_protocol')) {
            $settings['mailing_protocol'] = $this->string('mailing_protocol')->toString();
        }
        if ($this->has('mailing_smtp_host')) {
            $settings['mailing_smtp_host'] = $this->string('mailing_smtp_host')->toString();
        }
        if ($this->has('mailing_smtp_user')) {
            $settings['mailing_smtp_user'] = $this->string('mailing_smtp_user')->toString();
        }
        if ($this->has('mailing_smtp_pass')) {
            $settings['mailing_smtp_pass'] = $this->string('mailing_smtp_pass')->toString();
        }
        if ($this->has('mailing_smtp_port')) {
            $settings['mailing_smtp_port'] = $this->integer('mailing_smtp_port');
        }
        if ($this->has('mailing_smtp_timeout')) {
            $settings['mailing_smtp_timeout'] = $this->integer('mailing_smtp_timeout');
        }
        if ($this->has('mailing_smtp_crypto')) {
            $settings['mailing_smtp_crypto'] = $this->string('mailing_smtp_crypto')->toString();
        }

        return $settings;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.mailing'));
    }
}
