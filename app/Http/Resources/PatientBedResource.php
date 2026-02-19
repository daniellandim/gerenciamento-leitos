<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientBedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'patient'     => [
                'id'   => $this->patient->id,
                'name' => $this->patient->name,
                'cpf'  => $this->patient->cpf,
            ],
            'bed'         => [
                'id'          => $this->bed->id,
                'identifier'  => $this->bed->identifier,
                'description' => $this->bed->description,
            ],
            'admitted_at' => $this->admitted_at,
        ];
    }
}
