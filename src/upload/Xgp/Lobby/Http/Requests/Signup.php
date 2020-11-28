<?php

namespace App\Http\Requests;

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
            'character' => 'required|alpha_dash|min_length[3]|max_length[20]|is_unique[users.user_name]',
            'email' => 'required|valid_email|is_unique[users.user_email]',
            'password' => 'required|min_length[8]',
            'agb' => 'required',
        ];
    }
}
