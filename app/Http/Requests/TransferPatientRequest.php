<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferPatientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_bed_id' => ['required', 'integer', 'exists:beds,id'],
        ];
    }
}
