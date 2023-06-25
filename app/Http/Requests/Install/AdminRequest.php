<?php

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|max:255|unique:users,email',
            'username' => 'required|between:3,20|alpha_num:ascii|unique:users,name',
            'password' => 'required|min:8',
            'password-confirm' => 'required|min:8|required_with:password-confirm|same:password-confirm',
        ];
    }
}
