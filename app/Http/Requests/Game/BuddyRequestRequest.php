<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class BuddyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|integer|min:1|exists:users,id',
            'text' => 'nullable|string|max:5000',
        ];
    }

    public function targetUserId(): int
    {
        return (int) $this->validated()['user'];
    }

    public function messageText(): string
    {
        return strip_tags((string) ($this->validated()['text'] ?? ''));
    }
}
