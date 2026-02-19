<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FindBedRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cpf' => ['required', 'string', 'size:11'],
        ];
    }
}
