<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmitPatientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cpf'  => ['required', 'string', 'size:11'],
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
