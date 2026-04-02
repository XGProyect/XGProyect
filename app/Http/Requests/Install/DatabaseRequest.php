<?php

declare(strict_types=1);

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
    * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'driver' => 'required|in:mysql',
            'host' => 'required',
            'port' => 'nullable|integer',
            'database' => 'required',
            'username' => 'required',
            'password' => 'nullable',
            'prefix' => 'nullable',
        ];
    }
}
