<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'table' => 'required|array|min:1',
            'table.*' => ['required', 'string', Rule::in($this->getValidTableNames())],
            'optimize' => 'nullable',
            'repair' => 'nullable',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getValidTableNames(): array
    {
        return collect(DB::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE table_schema = ?',
            [config('DB_DATABASE')]
        ))->pluck('TABLE_NAME')->all();
    }
}
