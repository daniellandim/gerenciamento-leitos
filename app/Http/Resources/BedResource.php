<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $occupancy = $this->currentOccupancy;

        return [
            'id'          => $this->id,
            'identifier'  => $this->identifier,
            'description' => $this->description,
            'status'      => $occupancy ? 'occupied' : 'available',
            'patient'     => $occupancy ? [
                'id'          => $occupancy->patient->id,
                'name'        => $occupancy->patient->name,
                'cpf'         => $occupancy->patient->cpf,
                'admitted_at' => $occupancy->admitted_at,
            ] : null,
        ];
    }
}
