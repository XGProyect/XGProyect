<?php

namespace Xgp\Lobby\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Signup extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'character' => 'required|alpha_dash|between:3,20|unique:users,user_name',
            'email' => 'required|email|unique:users,user_email',
            'password' => 'required|min:8',
            'agb' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'character.unique' => __('lobby::home.hm_username_not_available'),
            'email.unique' => __('lobby::home.hm_email_not_available'),
        ];
    }
}
