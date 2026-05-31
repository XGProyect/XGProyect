<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Enums\SearchType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
            'searchType' => ['nullable', 'string', Rule::in(array_column(SearchType::cases(), 'value'))],
            'searchText' => 'nullable|string|max:64',
        ];
    }

    /**
     * Whether the user actually submitted a query (initial GET / empty POST
     * should just render the form, not show a "no results" error).
     */
    public function wasSubmitted(): bool
    {
        return $this->isMethod('post') && \is_string($this->input('searchText'));
    }

    public function searchType(): SearchType
    {
        $value = $this->input('searchType');

        if (\is_string($value)) {
            $parsed = SearchType::tryFrom($value);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return SearchType::PlayerName;
    }

    public function searchText(): string
    {
        return trim((string) $this->input('searchText', ''));
    }
}
