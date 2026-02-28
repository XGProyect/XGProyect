<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RepairRequest extends FormRequest
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
            'table'    => 'required|array|min:1',
            'table.*'  => 'required|string',
            'optimize' => 'nullable',
            'repair'   => 'nullable',
        ];
    }
}
