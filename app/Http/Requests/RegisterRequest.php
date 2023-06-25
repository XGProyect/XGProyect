<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    protected $errorBag = 'register';

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
            'username' => 'required|between:3,20|alpha_num:ascii|unique:users,name',
            'password' => 'required|min:8',
            'email' => 'required|max:255|unique:users,email',
            'agb' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.unique' => __('home/register.re_username_not_available'),
            'email.unique' => __('home/register.re_email_not_available'),
        ];
    }
}
