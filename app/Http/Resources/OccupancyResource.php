<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OccupancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'bed'           => [
                'id'         => $this->bed->id,
                'identifier' => $this->bed->identifier,
            ],
            'patient'       => [
                'id'   => $this->patient->id,
                'name' => $this->patient->name,
                'cpf'  => $this->patient->cpf,
            ],
            'admitted_at'   => $this->admitted_at,
            'discharged_at' => $this->discharged_at,
        ];
    }
}
