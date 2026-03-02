<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MessagesSearchRequest extends FormRequest
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
            'message_sender' => ['nullable', 'string', 'max:255'],
            'message_receiver' => ['nullable', 'string', 'max:255'],
            'message_subject' => ['nullable', 'string', 'max:255'],
            'message_date' => ['nullable', 'date_format:Y-m-d'],
            'message_type' => ['nullable', 'integer', 'min:0'],
            'message_text' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function hasFilters(): bool
    {
        return $this->hasAny(array_keys($this->rules()));
    }

    public function filterSender(): string
    {
        return trim($this->string('message_sender')->toString());
    }

    public function filterReceiver(): string
    {
        return trim($this->string('message_receiver')->toString());
    }

    public function filterSubject(): string
    {
        return trim($this->string('message_subject')->toString());
    }

    public function filterDate(): string
    {
        return trim($this->string('message_date')->toString());
    }

    public function filterType(): string
    {
        return $this->string('message_type')->toString();
    }

    public function filterText(): string
    {
        return trim($this->string('message_text')->toString());
    }

    /**
     * @return array<string, string>
     */
    public function searchValues(): array
    {
        return [
            'message_sender' => $this->filterSender(),
            'message_receiver' => $this->filterReceiver(),
            'message_subject' => $this->filterSubject(),
            'message_date' => $this->filterDate(),
            'message_type' => $this->filterType(),
            'message_text' => $this->filterText(),
        ];
    }
}
