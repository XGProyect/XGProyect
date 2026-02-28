<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResetRequest extends FormRequest
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
            'resetall'   => 'nullable',
            'defenses'   => 'nullable',
            'ships'      => 'nullable',
            'h_d'        => 'nullable',
            'edif_p'     => 'nullable',
            'edif_l'     => 'nullable',
            'edif'       => 'nullable',
            'inves'      => 'nullable',
            'inves_c'    => 'nullable',
            'ofis'       => 'nullable',
            'dark'       => 'nullable',
            'resources'  => 'nullable',
            'notes'      => 'nullable',
            'rw'         => 'nullable',
            'friends'    => 'nullable',
            'alliances'  => 'nullable',
            'fleets'     => 'nullable',
            'banneds'    => 'nullable',
            'messages'   => 'nullable',
            'statpoints' => 'nullable',
            'moons'      => 'nullable',
        ];
    }
}
