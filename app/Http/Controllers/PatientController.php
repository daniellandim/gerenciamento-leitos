<?php

namespace App\Http\Controllers;

use App\Http\Requests\FindBedRequest;
use App\Http\Resources\PatientBedResource;
use App\Services\BedService;

class PatientController extends Controller
{
    public function __construct(private readonly BedService $bedService)
    {
    }

    public function findBed(FindBedRequest $request)
    {
        try {
            $occupancy = $this->bedService->findBedByCpf($request->cpf);
            return PatientBedResource::make($occupancy);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }
}
