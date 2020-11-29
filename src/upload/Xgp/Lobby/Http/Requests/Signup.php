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
    public function rules()
    {
        return [
            'character' => 'required|alpha_dash|min:3|max:20|is_unique[users.user_name]',
            'email' => 'required|email|is_unique[users.user_email]',
            'password' => 'required|min:8',
            'agb' => 'required',
        ];
    }
}
